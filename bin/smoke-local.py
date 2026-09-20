#!/usr/bin/env python3
"""Exercise the isolated demo site over HTTP. Only loopback URLs are accepted.

Use after creating the synthetic admin/geschaeftsstelle/ov-musterstadt/ov-neuburg
accounts and seed data; credentials come exclusively from environment variables.
No email is sent by this script. Newsletter tests require a local mail interceptor.
"""
import http.cookiejar
import json
import os
import re
import urllib.error
import urllib.parse
import urllib.request

BASE = os.environ.get('GK_SMOKE_URL', 'http://localhost:8082').rstrip('/')
assert urllib.parse.urlparse(BASE).hostname in ('localhost', '127.0.0.1'), 'Local fixture only'
PASSWORD = os.environ['GK_SMOKE_PASSWORD']


class Client:
    def __init__(self, login, password=PASSWORD):
        self.http = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
        self.http.open(BASE + '/wp-login.php').read()
        data = urllib.parse.urlencode({'log': login, 'pwd': password, 'wp-submit': 'Log In', 'redirect_to': BASE + '/wp-admin/', 'testcookie': '1'}).encode()
        self.http.open(BASE + '/wp-login.php', data=data).read()
        html = self.http.open(BASE + '/wp-admin/post-new.php?post_type=gk_event').read().decode()
        match = re.search(r'createNonceMiddleware\(\s*["\x27]([^"\x27]+)', html)
        if not match:
            match = re.search(r'"nonce"\s*:\s*"([^"]+)"', html)
        assert match, f'No REST nonce for {login}'
        self.nonce = match[1]

    def api(self, path, method='GET', data=None):
        headers = {'X-WP-Nonce': self.nonce, 'Content-Type': 'application/json'}
        request = urllib.request.Request(BASE + '/wp-json/wp/v2/' + path, method=method, data=None if data is None else json.dumps(data).encode(), headers=headers)
        try:
            response = self.http.open(request)
            return response.status, json.loads(response.read())
        except urllib.error.HTTPError as error:
            return error.code, json.loads(error.read())

    def page_status(self, path):
        try:
            return self.http.open(BASE + path).status
        except urllib.error.HTTPError as error:
            return error.code


def expect(condition, message):
    assert condition, message
    print('PASS:', message)


office = Client('geschaeftsstelle')
ova = Client('ov-musterstadt')
ovb = Client('ov-neuburg')
admin = Client('admin', os.environ['GK_SMOKE_ADMIN_PASSWORD'])
status, terms = office.api('gk_zuordnung?per_page=100')
expect(status == 200, 'taxonomy available to office')
scopes = {term['slug']: term['id'] for term in terms}
a, b, kv = scopes['ov-musterstadt'], scopes['ov-neuburg'], scopes['kreisverband']
status, event = office.api('gk_event', 'POST', {'title': 'HTTP smoke OV event', 'status': 'publish', 'gk_zuordnung': [b], 'meta': {'gk_event_start_date': '2026-12-12', 'gk_event_start_time': '18:30', 'gk_event_end_date': '2026-12-12', 'gk_event_end_time': '20:00', 'gk_event_location': 'Synthetic test hall'}})
expect(status == 201 and event['gk_zuordnung'] == [b], 'office publishes event in another OV')
event_id = event['id']
status, _ = ovb.api(f'gk_event/{event_id}', 'POST', {'title': 'HTTP smoke OV admin correction'})
expect(status == 200, 'OV admin edits another author inside own OV')
status, _ = ova.api(f'gk_event/{event_id}', 'POST', {'title': 'Forbidden correction'})
expect(status == 403, 'foreign OV edit rejected')
status, own = ova.api('posts', 'POST', {'title': 'HTTP smoke author draft', 'status': 'draft'})
expect(status == 201 and own['gk_zuordnung'] == [a], 'OV author draft assigned automatically')
post_id = own['id']
status, _ = ova.api(f'posts/{post_id}', 'POST', {'gk_zuordnung': [b]})
expect(status == 403, 'foreign reassignment rejected')
status, _ = ova.api(f'posts/{post_id}', 'POST', {'status': 'publish'})
expect(status == 200, 'OV author publishes own draft')
status, _ = ova.api(f'posts/{post_id}?force=true', 'DELETE')
expect(status == 403, 'permanent deletion rejected')
status, trashed = ova.api(f'posts/{post_id}', 'DELETE')
expect(status == 200 and trashed['status'] == 'trash', 'published own post moves to trash')
status, restored = ova.api(f'posts/{post_id}', 'POST', {'status': 'draft'})
expect(status == 200 and restored['status'] == 'draft', 'post restored as draft')
status, _ = office.api(f'posts/{post_id}', 'POST', {'status': 'private', 'gk_zuordnung': [kv]})
expect(status == 200, 'office moves synthetic private post to KV')
status, _ = ova.api(f'posts/{post_id}')
expect(status == 403, 'former author cannot read now-foreign private content')
status, media = ova.api('media?per_page=100')
expect(status == 200 and all(m['gk_zuordnung'] == [a] for m in media), 'media REST collection contains only own OV')
expect(admin.page_status('/wp-admin/users.php?page=gk-role-overview') == 200, 'administrator role overview opens')
expect(office.page_status('/wp-admin/users.php?page=gk-role-overview') == 403, 'office cannot open role administration')
status, user = office.api('users/me')
expect(status == 200, 'office session remains valid')
print('All HTTP lifecycle checks passed. Synthetic content retained for manual inspection.')
