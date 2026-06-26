# Provider Profile UI Fallback Rules

**Clean, Professional Behavior for Missing/Empty Data**

---

## Core Principle

If a field is missing or empty, **HIDE IT**. Don't show null, undefined, empty sections, or placeholder text unless it directly helps the user understand what to do.

---

## Field-by-Field Fallback Rules

### Header Section

#### Business Name
- **If present:** Display exactly as provided
- **If missing:** Display user.name as fallback
- **If both missing:** Display "مقدم خدمة" (Service Provider)
- **Behavior:** NEVER null

```tsx
name: provider.name || 'مقدم خدمة'
```

#### Logo (Avatar)
- **If valid URL:** Display logo_url
- **If not URL/empty:** Show initials of business_name or user name
- **Fallback:** Use single-letter avatar with provider ID hash for color
- **Never:** Show broken image icon

```tsx
<Avatar 
  source={avatarUrl ? { uri: avatarUrl } : null}
  fallback={getInitials(provider.name)}
  color={hashColorForId(provider.id)}
/>
```

#### Cover Image
- **If valid URL and not placeholder:** Display cover_url
- **Else if portfolio_items[0].images[0] exists:** Display first portfolio image as cover
- **Else if logo exists and valid:** Blur and use logo as cover
- **Else:** Display solid gradient (theme-based)
- **Never:** Show gray placeholder area

```tsx
coverUrl = provider.cover_url (if valid)
         || provider.portfolio_items?.[0]?.images?.[0]
         || provider.logo_url (with blur filter)
         || null (render gradient instead)
```

#### Category / Subcategory Chip
- **If category present:** Display category name
- **Else if subcategories[0] present:** Display first subcategory name
- **Else:** HIDE this section completely
- **Color:** Use category color/icon if available

```tsx
{(provider.category?.name || provider.subcategories?.[0]?.name) && (
  <CategoryChip name={provider.category?.name || provider.subcategories[0].name} />
)}
```

#### City Tag
- **If city present:** Display "📍 City Name"
- **Else:** HIDE city tag completely

```tsx
{provider.city?.name && <CityTag name={provider.city.name} />}
```

#### Years Experience Badge
- **If years_experience = null or 0:** HIDE this badge
- **If years_experience = 1:** Display "سنة خبرة" (1 year of experience)
- **If years_experience > 1:** Display "X سنوات خبرة" (X years of experience)

```tsx
{provider.years_experience ? (
  <Badge>{provider.years_experience === 1 
    ? 'سنة خبرة' 
    : `${provider.years_experience} سنوات خبرة`}</Badge>
) : null}
```

---

### Action Buttons Row

#### WhatsApp Button
- **If whatsapp_url present and valid:** Show green WhatsApp button with icon
- **Else:** HIDE button completely (no disabled state)

```tsx
{provider.whatsapp_url && (
  <ActionButton 
    icon="logo-whatsapp" 
    label="واتساب"
    color="#25D366"
    onPress={() => openExternalUrl(provider.whatsapp_url)}
  />
)}
```

#### Call Button
- **If phone present:** Show blue call button with icon
- **Else:** HIDE button completely

```tsx
{provider.phone && (
  <ActionButton 
    icon="call"
    label="اتصل"
    color="#1877F2"
    onPress={() => Linking.openURL(`tel:${provider.phone}`)}
  />
)}
```

#### Map Button
- **If map_url present and valid:** Show green map button
- **Else:** HIDE button completely

```tsx
{provider.map_url && (
  <ActionButton 
    icon="map-outline"
    label="الخريطة"
    color="#34D399"
    onPress={() => openExternalUrl(provider.map_url)}
  />
)}
```

#### Composite Behavior
- **If NONE of (whatsapp, phone, map) present:** HIDE entire action row
- **If ONLY ONE present:** Display single full-width button
- **If MULTIPLE:** Display as flexed row (2-4 buttons)

```tsx
const hasAnyContact = !!(provider.whatsapp_url || provider.phone || provider.map_url);
if (!hasAnyContact) return null;

// Render buttons...
```

---

### Rating Section

#### Rating Stars + Count
- **If reviews_count = 0:** Hide entire rating section OR show "لا توجد تقييمات بعد"
- **If reviews_count > 0:** Display stars (1-5) + "X تقييم"
- **If rating_average = 0 but reviews exist:** Show partial stars based on count

```tsx
{provider.reviews_count > 0 ? (
  <RatingDisplay 
    average={provider.rating_average}
    count={provider.reviews_count}
  />
) : null}
```

---

### About Section

#### Bio/Description
- **If description present (not empty/whitespace):** Display in expandable section
- **Else:** HIDE "نبذة عنا" section completely
- **Max height:** 3 lines, then "عرض المزيد" button if longer
- **Sanitize:** Never display raw HTML or JSON

```tsx
if (!provider.description?.trim()) return null;

<Section title="نبذة عنا">
  <ExpandableText maxLines={3} text={provider.description} />
</Section>
```

#### Service Area Note
- **If service_area_note present (not empty):** Display in smaller text below bio
- **Else:** HIDE completely
- **Style:** Secondary color, smaller font

```tsx
{provider.service_area_note?.trim() && (
  <Text style={styles.secondaryText}>{provider.service_area_note}</Text>
)}
```

#### Remote Work Badge
- **If offers_remote_work = true:** Display "العمل عن بعد متاح" badge
- **If offers_remote_work = false:** HIDE completely (don't show "doesn't offer remote")

```tsx
{provider.offers_remote_work && (
  <RemoteWorkBadge />
)}
```

---

### Services Section

#### Subcategories List
- **If subcategories.length = 0:** HIDE entire section
- **If length = 1:** Display single full-width chip
- **If length > 1:** Display horizontal scroll chips
- **Format:** Show name only, clickable to filter/search

```tsx
if (!provider.subcategories?.length) return null;

<Section title="الخدمات المقدمة">
  <ChipList items={provider.subcategories} />
</Section>
```

---

### Social Links Row

#### Individual Social Icons
- **Facebook:** If present, show icon + clickable
- **Instagram:** If present, show icon + clickable
- **LinkedIn:** If present, show icon + clickable
- **GitHub:** If present, show icon + clickable
- **Website:** If present, show globe icon + clickable

**Rule: HIDE each individual icon if URL is missing**

```tsx
const socialIcons = [
  provider.website && { id: 'website', icon: 'globe-outline', url: provider.website },
  provider.social_links?.facebook && { id: 'facebook', icon: 'logo-facebook', url: provider.social_links.facebook },
  provider.social_links?.instagram && { id: 'instagram', icon: 'logo-instagram', url: provider.social_links.instagram },
  provider.social_links?.linkedin && { id: 'linkedin', icon: 'logo-linkedin', url: provider.social_links.linkedin },
  provider.social_links?.github && { id: 'github', icon: 'logo-github', url: provider.social_links.github },
].filter(Boolean);

if (socialIcons.length === 0) return null;

<SocialIconRow icons={socialIcons} />
```

---

### Portfolio / Projects Section

#### Gallery
- **If portfolio_items.length = 0:** HIDE entire section
- **If length = 1:** Display full-width single image with optional title
- **If length > 1:** Display adaptive grid (2-3 columns)
- **Never:** Show blank gray cells
- **Missing title:** Just show image, no text space

```tsx
if (!provider.portfolio_items?.length) return null;

<Section title="معرض الأعمال">
  {provider.portfolio_items.length === 1 ? (
    <FullWidthProjectCard project={provider.portfolio_items[0]} />
  ) : (
    <ProjectGrid projects={provider.portfolio_items} columns={2} />
  )}
</Section>
```

#### Project Card
- **Title:** Show if present, hide if null/empty
- **Description:** Show if present, hide if null/empty
- **Image:** Always show
- **Link:** If present, make entire card clickable

```tsx
<ProjectCard
  title={project.title || null}  // hide if falsy
  description={project.short_description || null}
  image={project.images[0]}
  onPress={project.link ? () => openExternalUrl(project.link) : null}
/>
```

---

### Credentials / Certifications Section

#### List
- **If credentials.length = 0:** HIDE entire section
- **If length > 0:** Display as list with date, issuer, title

```tsx
if (!provider.credentials?.length) return null;

<Section title="الشهادات والمؤهلات">
  <CredentialsList credentials={provider.credentials} />
</Section>
```

#### Credential Item
- **Title:** Always show
- **Issuer:** Show if present, hide if null
- **Issue Date:** Show if present (formatted: "Jun 2024")
- **Verification URL:** Show as clickable link if present
- **Notes:** Show if present

```tsx
<CredentialItem>
  <Text bold>{credential.title}</Text>
  {credential.issuer && <Text secondary>{credential.issuer}</Text>}
  {credential.issue_date && <Text small>{formatDate(credential.issue_date)}</Text>}
  {credential.verification_url && <Link url={credential.verification_url} />}
  {credential.notes && <Text secondary>{credential.notes}</Text>}
</CredentialItem>
```

---

### Reviews Section (if endpoint exists)

#### Reviews List
- **If reviews.length = 0:** HIDE entire section OR show "لا توجد تقييمات بعد"
- **If length > 0:** Show most recent reviews (paginated or first 3-5)

```tsx
if (!provider.reviews?.length) return null;

<Section title="التقييمات">
  <ReviewsList reviews={provider.reviews.slice(0, 5)} />
</Section>
```

---

## Missing vs. Empty Rules

### Null
```typescript
field === null  // Provider didn't fill it (OK to hide)
```

### Empty String
```typescript
field === ''  // Provider left it empty (hide, same as null)
```

### Whitespace
```typescript
field === '   '  // Treat as empty, trim first
```

### Implementation
```typescript
const isEmpty = (value: string | null | undefined): boolean =>
  !value || !value.trim();

// Usage
if (isEmpty(provider.service_area_note)) {
  // Hide it
}
```

---

## URL Validation

Before rendering any link button, validate the URL:

```typescript
function isValidUrl(url: string | null | undefined): boolean {
  if (!url) return false;
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

// Usage
{isValidUrl(provider.website) && (
  <ActionButton url={provider.website} />
)}
```

---

## Accessibility

1. **Color alone doesn't convey information** - Use icons + text labels
2. **All buttons must have text labels** (not just icons)
3. **Missing data should be invisible, not disabled**
4. **Arabic support** - Ensure right-to-left layout

---

## Empty State Examples

### Minimal Profile (Only Name + Category)
```
┌─────────────────────────────┐
│     [Gradient Background]    │
│     [Default Initials]       │
│     Provider Name            │
│                              │
│         📍 Category          │
│                              │
│  ┌─────────────────────────┐ │
│  │  لا توجد وسائل تواصل    │ │ (optional)
│  └─────────────────────────┘ │
│                              │
│  الخدمات المقدمة:            │
│  [Category Chip]             │
└─────────────────────────────┘
```

### Full Profile
```
┌─────────────────────────────┐
│      [Cover Image]          │
│   [Logo Overlay]            │
│   Provider Name             │
│   📍 City  |  Category      │
│                              │
│  ⭐⭐⭐⭐⭐ (5.0) 12 تقييم    │
│                              │
│  [WhatsApp] [Call] [Map]    │
│                              │
│  نبذة عنا:                   │
│  Lorem ipsum dolor...        │
│  عرض المزيد                  │
│                              │
│  العمل عن بعد متاح           │
│  5 سنوات خبرة                │
│                              │
│  الخدمات المقدمة:            │
│  [Chip1] [Chip2] [Chip3]   │
│                              │
│  معرض الأعمال:              │
│  [Project Grid]             │
│                              │
│  الشهادات والمؤهلات:         │
│  ✓ Certificate 1            │
│  ✓ Certificate 2            │
│                              │
│  [Social Icons Row]         │
└─────────────────────────────┘
```

---

## Implementation Checklist

- [ ] Use isEmpty() utility for all optional fields
- [ ] Use isValidUrl() for all URLs before rendering
- [ ] Hide entire sections if all child fields empty
- [ ] Use correct Arabic pluralization (1 vs many)
- [ ] No gray placeholder backgrounds
- [ ] No "غير محدد" fallback text
- [ ] No null/undefined visible to user
- [ ] All buttons must have labels
- [ ] All images must have fallback handling
- [ ] No hardcoded Unsplash URLs in mobile code
- [ ] Test with 10+ providers of varying completeness

---

## Next Phase

See: [provider-profile-final-fix-summary.md](provider-profile-final-fix-summary.md)
