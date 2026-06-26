# Image Upload Specifications for Delni Providers

## Overview

All provider images are automatically resized to specific, optimized dimensions when uploaded. This ensures consistent display across all devices and optimal performance.

---

## 📐 Enforced Image Dimensions

### 1. Profile Logo (Avatar)

**Enforced Size:** `240×240 pixels`
- **Format:** PNG or JPEG (converted to WebP on backend)
- **Max Upload Size:** 2 MB
- **Use Case:** Provider avatar in profile header and search results
- **Aspect Ratio:** 1:1 (square)
- **Backend Processing:** Images are cropped/covered to exact 240×240 (2x retina for 120×120 display)

**Provider Guidance:**
- Upload an image at least 240×240 pixels
- Preferably square (1:1 ratio)
- Any dimensions will be automatically cropped to fit

---

### 2. Profile Cover/Background Image

**Enforced Size:** `1080×540 pixels`
- **Format:** JPEG recommended (converted to WebP on backend)
- **Max Upload Size:** 4 MB
- **Use Case:** Hero image on provider profile screen
- **Aspect Ratio:** 2:1 (landscape)
- **Backend Processing:** Images are cropped to exact 1080×540

**Provider Guidance:**
- Upload a landscape image (wider than it is tall)
- Minimum dimensions: 1080×540 pixels
- Recommended aspect ratio: 2:1
- Safe zone: Center 90% of image will be visible
- The image will be automatically cropped from center if needed

---

### 3. Portfolio Project Images

**Enforced Size:** `1080×1080 pixels`
- **Format:** JPEG recommended (converted to WebP on backend)
- **Max Upload Size:** 4 MB per image
- **Use Case:** Gallery images in portfolio
- **Aspect Ratio:** 1:1 (square) or 4:3 (landscape)
- **Backend Processing:** Images are cropped to exact 1080×1080 (square)
- **Max Images:** 4 per project, 8 across all projects

**Provider Guidance:**
- Upload square (1:1) or landscape images
- Minimum dimensions: 1080×1080 pixels
- The image will be automatically cropped to square format if needed
- Center composition recommended for square crops

---

## 🔧 Technical Implementation

### Backend Service: ProfileImageService

Location: `app/Services/ProfileImageService.php`

The service automatically handles:
- ✅ Image validation (MIME type, file integrity)
- ✅ Dimension enforcement (exact pixel resizing)
- ✅ Format conversion (PNG/JPEG → WebP)
- ✅ Quality optimization (60-75% based on dimensions)
- ✅ File storage (organized by type and UUID)
- ✅ Old file cleanup (removes previous uploads)

### Upload Processing Flow

```
Provider Uploads → Filament Form → ProfileImageService
                                  ↓
                         Image Validation
                                  ↓
                         Load & Decode Image
                                  ↓
                         Enforce Exact Dimensions
                                  ↓
                         Convert to WebP Format
                                  ↓
                         Optimize Quality (60-75%)
                                  ↓
                         Store with UUID Filename
                                  ↓
                         Delete Old File
```

---

## 📱 Mobile Display Sizes

These are the display sizes on the React Native mobile app:

| Component | Display Size | Backend Stored | Retina Multiplier |
|-----------|-------------|-----------------|-------------------|
| Logo | 120×120 px | 240×240 px | 2x |
| Cover | ~100-200 px height | 1080×540 px | 2x |
| Portfolio | 100-150 px | 1080×1080 px | ~7x |

---

## 🎯 Quality & Format

- **Output Format:** WebP (industry standard, 25-34% smaller than PNG/JPEG)
- **Quality Levels:**
  - Images > 2,560,000 pixels: 60% quality
  - Images > 640,000 pixels: 70% quality
  - Smaller images: 75% quality
- **Color Space:** sRGB (standard for web/mobile)
- **File Organization:**
  - Avatars: `storage/public/profiles/avatars/{uuid}.webp`
  - Covers: `storage/public/profiles/covers/{uuid}.webp`
  - Portfolio: `storage/public/portfolio/images/{uuid}.webp`

---

## ✅ Upload Validation Rules

When a provider uploads an image, these rules apply:

1. **Accepted Formats:** JPEG, PNG, WebP only
2. **File Size Limits:**
   - Avatar: ≤ 2 MB
   - Cover: ≤ 4 MB
   - Portfolio: ≤ 4 MB
3. **Image Validity:** Must be a valid, readable image
4. **Automatic Processing:**
   - ✅ Invalid format → Rejected
   - ✅ File too large → Rejected
   - ✅ Image corrupted → Rejected
   - ✅ Wrong dimensions → Automatically cropped/resized
   - ✅ Wrong format (PNG) → Converted to WebP

---

## 📊 Recommended Upload Dimensions

For best results, providers should upload images sized as follows:

| Type | Recommended Upload | Will Be Resized To |
|------|-------------------|------------------|
| Logo | 240×240 to 512×512 | 240×240 |
| Cover | 1080×540 to 2160×1080 | 1080×540 |
| Portfolio | 1080×1080 to 2160×2160 | 1080×1080 |

---

## 🚀 Performance Benefits

Enforcing exact dimensions provides:

1. **Faster Loads:** Optimal file sizes for mobile networks
2. **Consistent UI:** Predictable image dimensions in layouts
3. **Better SEO:** Proper image sizing improves Core Web Vitals
4. **Reduced Bandwidth:** WebP format saves 25-34% file size
5. **Caching:** Consistent dimensions improve cache hit rates
6. **Database:** Reduced storage requirements via format conversion

---

## 📋 Filament UI Instructions

### For Profile Logo Upload
```
Helper Text: "صورة مربعة (240×240 بكسل على الأقصى). سيتم تحويلها تلقائياً إلى حجم مثالي. (الحد الأقصى 2 MB)"
Translation: "Square image (240×240 pixels minimum). Will be automatically resized to optimal size. (Max 2 MB)"
```

### For Profile Cover Upload
```
Helper Text: "صورة أفقية بنسبة 2:1 (1080×540 بكسل على الأقصى). سيتم تحويلها تلقائياً إلى حجم مثالي. (الحد الأقصى 4 MB)"
Translation: "Landscape image with 2:1 ratio (1080×540 pixels minimum). Will be automatically resized to optimal size. (Max 4 MB)"
```

### For Portfolio Images Upload
```
Helper Text: "صورة مربعة أو أفقية (1080×1080 بكسل على الأقصى). سيتم تحويلها تلقائياً إلى حجم مثالي. (الحد الأقصى 4 MB)"
Translation: "Square or landscape image (1080×1080 pixels minimum). Will be automatically resized to optimal size. (Max 4 MB)"
```

---

## 🔄 Updates to Share with Providers

Send this to providers for their reference:

> **Image Upload Requirements**
>
> **Logo (Profile Avatar)**
> - Size: 240×240 pixels minimum
> - Format: JPG, PNG, or WebP
> - Max 2 MB
> - Will be automatically resized to 240×240
>
> **Cover Photo (Profile Background)**
> - Size: 1080×540 pixels minimum
> - Format: JPG, PNG, or WebP
> - Aspect Ratio: 2:1 (landscape)
> - Max 4 MB
> - Will be automatically resized to 1080×540
>
> **Portfolio Images**
> - Size: 1080×1080 pixels minimum
> - Format: JPG, PNG, or WebP
> - Max 4 MB per image
> - Will be automatically resized to 1080×1080
> - Up to 4 images per project
>
> All images are automatically converted to WebP format for optimal performance.
> Files outside these specs will be automatically resized.

---

## 🔍 Verification

To verify the implementation is working:

1. Upload a profile logo (any size)
2. Check storage: `storage/public/profiles/avatars/{uuid}.webp` → should be exactly 240×240
3. Upload a cover image (any aspect ratio)
4. Check storage: `storage/public/profiles/covers/{uuid}.webp` → should be exactly 1080×540
5. Upload a portfolio image
6. Check storage: `storage/public/portfolio/images/{uuid}.webp` → should be exactly 1080×1080

---

## 📞 Support

If providers have questions about image sizes:
- Images are automatically resized, so they don't need exact dimensions
- Just upload the best quality image they have
- The system will optimize it automatically
- All images are saved as WebP for faster loading

---

**Last Updated:** 2026-06-26
**Status:** Active - All sizes enforced via ProfileImageService
