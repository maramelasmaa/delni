<?php

namespace Tests\Unit;

use App\Services\ArabicNormalizationService;
use PHPUnit\Framework\TestCase;

class ArabicNormalizationTest extends TestCase
{
    private ArabicNormalizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ArabicNormalizationService;
    }

    /**
     * Test: Hamza variants normalize to same form.
     *
     * أ، إ، آ، ٱ should all normalize to ا
     */
    public function test_hamza_variants_normalize_consistently(): void
    {
        $withMaddaAbove = 'أحمد';    // أ (hamza above)
        $withHamzaBelow = 'إحمد';    // إ (hamza below)
        $withMadda = 'آحمد';         // آ (madda)
        $withWasla = 'ٱحمد';         // ٱ (wasla)

        $result1 = $this->service->normalize($withMaddaAbove);
        $result2 = $this->service->normalize($withHamzaBelow);
        $result3 = $this->service->normalize($withMadda);
        $result4 = $this->service->normalize($withWasla);

        // All should normalize to the same value
        $this->assertEquals($result1, $result2);
        $this->assertEquals($result2, $result3);
        $this->assertEquals($result3, $result4);

        // Should contain base alef
        $this->assertStringContainsString('احمد', $result1);
    }

    /**
     * Test: Diacritics (tashkeel) are removed.
     *
     * Fatha, damma, kasra, etc. should be stripped.
     */
    public function test_diacritics_removed(): void
    {
        $withFatha = 'تَقْنِيَّة';     // With various diacritics
        $withoutMarks = 'تقنية';       // Without diacritics

        $result = $this->service->normalize($withFatha);

        // Should match normalized version without marks
        $this->assertStringContainsString('تقنيه', $result);
    }

    /**
     * Test: Ta variants (ة ↔ ه) normalize consistently.
     */
    public function test_ta_marbuta_variants_normalize(): void
    {
        $withTaMarbuta = 'تقنية';      // ة (ta marbuta)
        $withHa = 'تقنيه';             // ه (ha)

        $result1 = $this->service->normalize($withTaMarbuta);
        $result2 = $this->service->normalize($withHa);

        // Both should normalize to same form
        $this->assertEquals($result1, $result2);
        $this->assertStringContainsString('تقنيه', $result1);
    }

    /**
     * Test: Alef maksura (ى) normalizes to ya (ي).
     */
    public function test_alef_maksura_normalizes_to_ya(): void
    {
        $withAlefMaksura = 'أسنان';    // Wait, let me use correct example
        $withAlefMaksura = 'موسى';     // ى (alef maksura)
        $withYa = 'موسي';              // ي (ya)

        $result1 = $this->service->normalize($withAlefMaksura);
        $result2 = $this->service->normalize($withYa);

        // Both should normalize to same form
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test: Real-world provider search scenario - احمد vs أحمد.
     */
    public function test_real_world_ahmad_variant_search(): void
    {
        $searchQuery = 'احمد';
        $providerName1 = 'أحمد للسباكة';
        $providerName2 = 'احمد للسباكة';

        $normalizedQuery = $this->service->normalize($searchQuery);
        $normalizedName1 = $this->service->normalize($providerName1);
        $normalizedName2 = $this->service->normalize($providerName2);

        // Both provider names should match search query after normalization
        $this->assertStringContainsString($normalizedQuery, $normalizedName1);
        $this->assertStringContainsString($normalizedQuery, $normalizedName2);
        $this->assertEquals($normalizedName1, $normalizedName2);
    }

    /**
     * Test: Real-world teeth specialist scenario - اسنان vs أسنان.
     */
    public function test_real_world_asnan_variant_search(): void
    {
        $searchQuery = 'اسنان';
        $providerName = 'أخصائي أسنان';

        $normalizedQuery = $this->service->normalize($searchQuery);
        $normalizedName = $this->service->normalize($providerName);

        // Should find "أسنان" when searching for "اسنان"
        $this->assertStringContainsString($normalizedQuery, $normalizedName);
    }

    /**
     * Test: Tatweel (Arabic stretching) removed.
     */
    public function test_tatweel_removed(): void
    {
        $withTatweel = 'أحـــمـــد';    // ـ (tatweel)
        $withoutTatweel = 'أحمد';

        $result1 = $this->service->normalize($withTatweel);
        $result2 = $this->service->normalize($withoutTatweel);

        // Should be identical after normalization
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test: Multiple spaces normalized to single space.
     */
    public function test_extra_spaces_normalized(): void
    {
        $multiSpace = 'أحمد    الخياط';
        $singleSpace = 'أحمد الخياط';

        $result1 = $this->service->normalize($multiSpace);
        $result2 = $this->service->normalize($singleSpace);

        // Should be identical
        $this->assertEquals($result1, $result2);
        // Should have single space between words
        $this->assertStringContainsString('احمد الخياط', $result1);
    }

    /**
     * Test: English letters lowercased.
     */
    public function test_english_letters_lowercased(): void
    {
        $mixed = 'Ahmed 123';
        $result = $this->service->normalize($mixed);

        // Should be lowercase
        $this->assertStringContainsString('ahmed', $result);
        $this->assertStringNotContainsString('Ahmed', $result);
    }

    /**
     * Test: Null and empty strings handled safely.
     */
    public function test_null_and_empty_string_safe(): void
    {
        $resultNull = $this->service->normalize(null);
        $resultEmpty = $this->service->normalize('');
        $resultSpace = $this->service->normalize('   ');

        $this->assertEquals('', $resultNull);
        $this->assertEquals('', $resultEmpty);
        $this->assertEquals('', $resultSpace);
    }

    /**
     * Test: Arabic detection.
     */
    public function test_arabic_detection(): void
    {
        $this->assertTrue($this->service->containsArabic('أحمد'));
        $this->assertTrue($this->service->containsArabic('hello أحمد'));
        $this->assertFalse($this->service->containsArabic('Ahmed'));
        $this->assertFalse($this->service->containsArabic('123'));
    }

    /**
     * Test: Mixed Arabic/English normalized.
     */
    public function test_mixed_arabic_english(): void
    {
        $mixed = 'أحمد Ahmed سباكة Plumbing';
        $result = $this->service->normalize($mixed);

        // Should contain normalized Arabic (ة becomes ه) and lowercase English
        $this->assertStringContainsString('احمد', $result);
        $this->assertStringContainsString('ahmed', $result);
        $this->assertStringContainsString('سباكه', $result); // ة normalized to ه
        $this->assertStringContainsString('plumbing', $result);
    }

    /**
     * Test: Real marketplace searches - common Libyan spellings.
     */
    public function test_common_libyan_marketplace_searches(): void
    {
        $searches = [
            'اخصائي' => 'أخصائي',  // specialist
            'دهان' => 'دهّان',      // painter
            'كهربائي' => 'كهربائي',  // electrician
            'سباك' => 'سباك',       // plumber
            'حلاق' => 'حلاق',       // barber
            'خياط' => 'خياط',       // tailor
        ];

        foreach ($searches as $informal => $formal) {
            $normalizedInformal = $this->service->normalize($informal);
            $normalizedFormal = $this->service->normalize($formal);

            // Both variants should normalize to same base form
            $this->assertEquals($normalizedInformal, $normalizedFormal,
                "Search '{$informal}' should match '{$formal}'"
            );
        }
    }

    /**
     * Test: Complex real-world provider names.
     */
    public function test_complex_provider_names(): void
    {
        $providerNames = [
            'أحمد للتقنية الحديثة',
            'احمد للتقنيه الحديثه',
            'أحمـــد للتقنيّة الحديثة',
        ];

        $normalizedNames = array_map(fn ($name) => $this->service->normalize($name), $providerNames);

        // All variations should normalize to same form
        $first = reset($normalizedNames);
        foreach ($normalizedNames as $normalized) {
            $this->assertEquals($first, $normalized);
        }
    }

    /**
     * Test: Search consistency both ways.
     *
     * If user searches for A and provider has B, they should match.
     * This tests normalization is consistent (idempotent).
     */
    public function test_normalization_idempotent(): void
    {
        $text = 'أحمد للسباكة';
        $normalized1 = $this->service->normalize($text);
        $normalized2 = $this->service->normalize($normalized1);
        $normalized3 = $this->service->normalize($normalized2);

        // Normalizing multiple times should give same result
        $this->assertEquals($normalized1, $normalized2);
        $this->assertEquals($normalized2, $normalized3);
    }
}
