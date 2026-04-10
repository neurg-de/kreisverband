# Grünes Design System

Source: https://design.gruene.de/4ccd94a80/p/31e403-intro
Organization: Bündnis 90/Die Grünen

## Colors

### Base Colors

| Token           | Hex       | Alias       |
|-----------------|-----------|-------------|
| black           | #000000   |             |
| white           | #FFFFFF   |             |
| primary-600     | #008939   | "Klee"      |
| secondary-600   | #005538   | "Tanne"     |
| grashalm-600    | #8ABD24   | "Grashalm"  |
| himmel-600      | #0BA1DD   | "Himmel"    |
| neutral-600     | #F5F1E9   | "Sand"      |
| sun-600         | #FFF17A   | "Sonne"     |

### Primary ("Klee") Scale

Used sparingly in digital; mainly for illustrations. Prefer Secondary ("Tanne") for interactive elements.

| Token        | Hex     |
|--------------|---------|
| primary-50   | #E5F3EB |
| primary-100  | #CCE7D7 |
| primary-200  | #B2DCC4 |
| primary-300  | #99D0B0 |
| primary-400  | #66B888 |
| primary-500  | #33A161 |
| primary-600  | #008939 |
| primary-700  | #006E2E |
| primary-800  | #005222 |
| primary-900  | #003717 |
| primary-950  | #002911 |

### Secondary ("Tanne") Scale

Main digital green tone. Used for buttons, links, checkboxes, and in darker shades for footer backgrounds.

| Token          | Hex     |
|----------------|---------|
| secondary-50   | #D5EEE6 |
| secondary-100  | #BEDDD2 |
| secondary-200  | #A6CCBF |
| secondary-300  | #8EBBAB |
| secondary-400  | #5F9885 |
| secondary-500  | #2F765E |
| secondary-600  | #005538 |
| secondary-700  | #00432C |
| secondary-800  | #003221 |
| secondary-900  | #002216 |

### Grey / Neutral ("Sand") Scale

Background surfaces, subtle hover styles, and illustrations.

| Token        | Hex     |
|--------------|---------|
| neutral-500  | #F7F4ED |
| neutral-600  | #F5F1E9 |
| neutral-700  | #EFE8DB |

### Sun ("Sonne")

Reserved for logo/sunflower and illustrations only.

| Token    | Hex     |
|----------|---------|
| sun-600  | #FFF17A |

### Himmel (Sky)

For illustrations and accent badges. 3 shades available.

| Token       | Hex     |
|-------------|---------|
| himmel-500  | #3CB4E4 |
| himmel-600  | #0BA1DD |
| himmel-700  | #0981B1 |

### Grashalm (Grass)

Not currently used in digital. Available in 3 shades.

| Token          | Hex     |
|----------------|---------|
| grashalm-500   | #A1CA50 |
| grashalm-600   | #8ABD24 |
| grashalm-700   | #6E971D |

### Semantic / Role Colors (from theme properties)

| Role                        | Value   |
|-----------------------------|---------|
| Accent / Interactive        | #005538 (secondary-600 "Tanne") |
| Link color                  | #005538 |
| Text primary                | #002216 (secondary-900) |
| Background                  | #F5F1E9 (neutral-600 "Sand") |
| Footer links                | #005538 |
| Status: positive            | #008939 (primary-600) |
| Status: neutral             | #0BA1DD (himmel-600) |
| Status: warning             | #FFF17A (sun-600) |
| Status: negative            | #c51d54 |
| Annotation marker           | #c51d54 |
| Table border/bg             | #F5F1E9 |
| Sidebar active/hover bg     | #FFF17A |

---

## Typography

### Font Families

- **GrueneType Neue** (weight 400) -- for large, bold headings only
  - Source: https://www.gruene.de/fonts/GrueneTypeNeue-Regular.woff
- **PT Sans** (400 + 700) -- body text, subheadings, UI elements, navigation
  - Source: Google Fonts

### GrueneType Neue Sizes

| Token   | Size       | Rem      | Line Height | Letter Spacing |
|---------|------------|----------|-------------|----------------|
| g-l     | 30px       | 1.875rem | 110%        | 0px            |
| g-xl    | 40px       | 2.5rem   | 110%        | 0px            |
| g-xxl   | 60px       | 3.75rem  | 110%        | 0px            |
| g-xxxl  | 80px       | 5rem     | 110%        | 0px            |

### PT Sans -- Subheadlines (weight 700)

| Token              | Size  | Rem        | Line Height | Letter Spacing |
|--------------------|-------|------------|-------------|----------------|
| g-subheadline-l    | 25px  | 1.5625rem  | 130%        | 0px            |
| g-subheadline      | 20px  | 1.25rem    | 130%        | 0px            |
| g-subheadline-m    | 18px  | 1.125rem   | 130%        | 0px            |
| g-subheadline-s    | 16px  | 1rem       | 130%        | 0px            |
| g-subheadline-xs   | 13px  | 0.8125rem  | 160%        | 0px            |

### PT Sans -- Body (weight 400)

| Token       | Size  | Rem        | Line Height     | Letter Spacing |
|-------------|-------|------------|-----------------|----------------|
| g-body-l    | 25px  | 1.5625rem  | 134%            | 0px            |
| g-body      | 20px  | 1.25rem    | 35px / 2.1875rem| 0px            |
| g-body-m    | 18px  | 1.125rem   | 28px / 1.75rem  | 0px            |
| g-body-s    | 16px  | 1rem       | 150%            | 0px            |
| g-body-xs   | 13px  | 0.8125rem  | 160%            | 0px            |

### PT Sans -- UI Elements (weight 400)

| Token          | Size  | Rem       | Line Height | Letter Spacing |
|----------------|-------|-----------|-------------|----------------|
| g-button-l     | 16px  | 1rem      | 100%        | 0px            |
| g-button-m     | 12px  | 0.75rem   | 100%        | 0.1px          |
| g-input        | 16px  | 1rem      | 120%        | 0px            |
| g-input-helper | 13px  | 0.8125rem | 120%        | 0px            |
| g-input-label  | 16px  | 1rem      | 130%        | 0px            |

---

## Spacings

Unified scale (base rem = 16px):

| px   | rem     |
|------|---------|
| 0    | 0       |
| 2    | 0.125   |
| 4    | 0.25    |
| 8    | 0.5     |
| 12   | 0.75    |
| 16   | 1       |
| 24   | 1.5     |
| 32   | 2       |
| 40   | 2.5     |
| 48   | 3       |
| 56   | 3.5     |
| 64   | 4       |
| 80   | 5       |
| 96   | 6       |
| 160  | 10      |

---

## Components (Bausteine)

All component pages: https://design.gruene.de/4ccd94a80/p/917c0e-bausteine

### Links
- **On light background:** secondary-600 (#005538), no underline by default, underline on hover
- **On dark background:** white (#FFFFFF), no underline by default, underline on hover
- **Variant with text-bullet:** link preceded by a decorative bullet
- **States:** default, hover, active (pressed), visited
- **Size variants:** inherit from parent text size (body-xs through body-l)

### Buttons

Three types, two sizes (S and M):

#### Primary Button
- **Background:** secondary-600 (#005538)
- **Text color:** white (#FFFFFF)
- **Font:** PT Sans, 16px (g-button-l) for size M, 12px (g-button-m) for size S
- **Line height:** 100%
- **Border-radius:** 4px
- **Padding:** ~12px 24px (M), ~8px 16px (S)
- **States:**
  - Default: #005538 bg, white text
  - Hover: #00432C (secondary-700) bg
  - Focus: #005538 bg + 2px outline offset
  - Disabled: opacity 0.4, cursor not-allowed
- **Optional icon:** quadratic icon in text color, placed before or after text

#### Icon Only Button
- Square button with icon only, no text
- Same color scheme as Primary Button

#### Filter Button
- Used for filter/tag toggles
- Outlined style: transparent bg, #005538 border, #005538 text
- Active/selected: filled with #005538 bg, white text

### Form Elements

All use PT Sans, secondary-600 (#005538) for active/checked states.

#### Input
- **Font:** PT Sans 16px (g-input), line-height 120%
- **Border:** 1px solid, neutral color, rounded 4px
- **States:** default, hover (darker border), active/focus (secondary-600 border), typing, disabled (opacity)
- **Variants:** input only, with label, with label + helper text

#### Labels
- **Font:** PT Sans 16px (g-input-label), line-height 130%
- **Color:** secondary-900 (#002216)

#### Helper Text
- **Font:** PT Sans 13px (g-input-helper), line-height 120%
- **Color:** lighter text, placed below input

#### Checkbox
- **States:** on/off × enabled/disabled/focus
- **Checked color:** secondary-600 (#005538)
- **Label position:** none, left, or right
- Note: "Aktuell in Bearbeitung" (currently in progress)

#### Radiobutton
- Same state/color pattern as Checkbox

#### Select (Dropdown)
- Same base styling as Input
- Includes dropdown arrow icon

#### Switch (Toggle)
- On/off toggle
- Active color: secondary-600 (#005538)

---

## Theme Configuration

```
Accent color:        #005538 (secondary-600)
Link color:          #005538
Background:          #F5F1E9 (neutral-600)
Text color:          #002216 (secondary-900)
Heading font:        GrueneType Neue
Body/Nav font:       PT Sans
Base rem:            16px
```
