import { router } from 'expo-router';
import { useEffect, useState } from 'react';
import { FlatList, Pressable, ScrollView, StyleSheet, Text, TextInput, View, Image, Modal, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ProviderCard } from '../../components/ProviderCard';
import { EmptyState, LoadingState } from '../../components/ScreenState';
import { Colors } from '../../constants/colors';
import api from '../../lib/api';
import { unwrap } from '../../lib/apiResponse';
import type { ApiEnvelope, HomePayload, NamedResource, ProviderCard as ProviderCardType } from '../../types/api';

export default function HomeScreen() {
  const [search, setSearch] = useState('');
  const [payload, setPayload] = useState<HomePayload | null>(null);
  const [loading, setLoading] = useState(true);
  const [cities, setCities] = useState<NamedResource[]>([]);
  const [selectedCity, setSelectedCity] = useState<NamedResource | null>(null);
  const [showCityModal, setShowCityModal] = useState(false);

  const fetchHomeData = (citySlug?: string) => {
    setLoading(true);
    const url = citySlug ? `/home?city=${citySlug}` : '/home';
    api.get<ApiEnvelope<HomePayload>>(url)
      .then(({ data }) => setPayload(unwrap(data)))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchHomeData();

    api.get<ApiEnvelope<NamedResource[]>>('/cities')
      .then(({ data }) => setCities(unwrap(data)))
      .catch(() => {});
  }, []);

  const handleCitySelect = (city: NamedResource | null) => {
    setSelectedCity(city);
    setShowCityModal(false);
    fetchHomeData(city?.slug);
  };

  const runSearch = () => {
    const term = search.trim();
    if (term) {
      const cityQuery = selectedCity ? `&city=${selectedCity.slug}` : '';
      router.push(`/search?q=${encodeURIComponent(term)}${cityQuery}`);
    }
  };

  if (loading && !payload) return <LoadingState />;
  if (!payload) return <EmptyState title="تعذر تحميل الصفحة الرئيسية." actionLabel="إعادة المحاولة" onAction={() => router.replace('/')} />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      {/* Header Bar */}
      <View style={styles.headerBar}>
        <Pressable style={styles.citySelector} onPress={() => setShowCityModal(true)}>
          <Text style={styles.citySelectorChevron}>▼</Text>
          <Text style={styles.citySelectorText} numberOfLines={1}>
            {selectedCity ? selectedCity.name : 'اختر المدينة'}
          </Text>
          <Text style={styles.citySelectorIcon}>📍</Text>
        </Pressable>

        <View style={styles.logoRow}>
          <Text style={styles.logoText}>دلني</Text>
          <Image source={require('../../assets/icon.png')} style={styles.logoImage} />
        </View>
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        {/* Search Section */}
        <View style={styles.searchSection}>
          <View style={styles.searchBox}>
            <TextInput
              style={styles.searchInput}
              placeholder="ابحث عن خدمة أو مقدم خدمة..."
              placeholderTextColor={Colors.textMuted}
              value={search}
              onChangeText={setSearch}
              onSubmitEditing={runSearch}
              returnKeyType="search"
              textAlign="right"
            />
            <Pressable style={styles.searchBtn} onPress={runSearch}>
              <Text style={styles.searchBtnIcon}>🔍</Text>
            </Pressable>
          </View>
        </View>

        {/* Specialties Section */}
        <SectionHeader title="التخصصات" action="عرض الكل" onPress={() => router.push('/categories')} />
        <FlatList
          data={payload.categories}
          horizontal
          inverted
          showsHorizontalScrollIndicator={false}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.categoryRail}
          renderItem={({ item }) => <CategoryChip category={item} />}
        />

        {/* Featured Providers Section */}
        {payload.featured_providers && payload.featured_providers.length > 0 ? (
          <View style={styles.section}>
            <SectionHeader title="مقدمو خدمات مميزون" />
            <View style={styles.providersList}>
              {payload.featured_providers.map((provider) => (
                <ProviderCard key={provider.id} provider={provider as ProviderCardType} />
              ))}
            </View>
          </View>
        ) : null}

        {/* Suggested Providers Section */}
        {payload.suggested_providers && payload.suggested_providers.length > 0 ? (
          <View style={styles.section}>
            <SectionHeader title="مزودون مقترحون" />
            <View style={styles.providersList}>
              {payload.suggested_providers.map((provider) => (
                <ProviderCard key={provider.id} provider={provider as ProviderCardType} />
              ))}
            </View>
          </View>
        ) : null}

        {/* Stats Section */}
        <View style={styles.statsSection}>
          <Text style={styles.statsKicker}>الإحصائيات</Text>
          <Text style={styles.statsTitle}>دلني بالأرقام</Text>

          <View style={styles.statsGrid}>
            <View style={styles.statsRow}>
              <StatCard value={payload.stats.reviews_count} label="تقييم" icon="☆" />
              <StatCard value={payload.stats.visible_providers_count} label="مقدم خدمة" icon="👥" />
            </View>
            <View style={styles.statsRow}>
              <StatCard value={payload.stats.cities_count} label="مدينة" icon="📍" />
              <StatCard value={payload.stats.categories_count} label="تخصص" icon="📖" />
            </View>
          </View>
        </View>

        {/* Provider CTA Banner */}
        <View style={styles.lpCta}>
          <View style={styles.lpCtaTextContainer}>
            <Text style={styles.lpCtaKicker}>تقدم خدمة؟</Text>
            <Text style={styles.lpCtaTitle}>اجعل ملفك مرئياً للعملاء</Text>
          </View>
          <Pressable style={styles.lpCtaBtn} onPress={() => router.push('/contact')}>
            <Text style={styles.lpCtaBtnText}>سجّل كمقدم خدمة</Text>
          </Pressable>
        </View>
      </ScrollView>

      {/* City Selector Modal */}
      <Modal visible={showCityModal} animationType="slide" transparent={true}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>اختر المدينة</Text>
            <ScrollView style={styles.cityList}>
              <Pressable
                style={[styles.cityItem, selectedCity === null && styles.cityItemActive]}
                onPress={() => handleCitySelect(null)}
              >
                <Text style={[styles.cityItemText, selectedCity === null && styles.cityItemTextActive]}>كل المدن</Text>
              </Pressable>
              {cities.map((city) => (
                <Pressable
                  key={city.id}
                  style={[styles.cityItem, selectedCity?.id === city.id && styles.cityItemActive]}
                  onPress={() => handleCitySelect(city)}
                >
                  <Text style={[styles.cityItemText, selectedCity?.id === city.id && styles.cityItemTextActive]}>
                    {city.name}
                  </Text>
                </Pressable>
              ))}
            </ScrollView>
            <Pressable style={styles.modalCloseBtn} onPress={() => setShowCityModal(false)}>
              <Text style={styles.modalCloseBtnText}>إلغاء</Text>
            </Pressable>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

function StatCard({ value, label, icon }: { value: number; label: string; icon: string }) {
  return (
    <View style={styles.statCard}>
      <View style={styles.statContent}>
        <Text style={styles.statValue}>{value}</Text>
        <Text style={styles.statLabel}>{label}</Text>
      </View>
      <View style={styles.statIconContainer}>
        <Text style={styles.statIconText}>{icon}</Text>
      </View>
    </View>
  );
}

function SectionHeader({ title, action, onPress }: { title: string; action?: string; onPress?: () => void }) {
  return (
    <View style={styles.sectionHeader}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {action && onPress ? (
        <Pressable onPress={onPress}>
          <Text style={styles.sectionAction}>{action}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

function CategoryChip({ category }: { category: NamedResource }) {
  return (
    <Pressable style={styles.categoryChip} onPress={() => router.push(`/category/${category.slug}`)}>
      <Text style={styles.categoryText}>{category.name}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  content: { paddingBottom: 28 },
  headerBar: {
    backgroundColor: Colors.white,
    borderBottomColor: Colors.border,
    borderBottomWidth: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  logoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  logoText: {
    color: Colors.navy,
    fontFamily: 'Cairo-Black',
    fontSize: 20,
  },
  logoImage: {
    width: 32,
    height: 32,
    borderRadius: 8,
  },
  citySelector: {
    flexDirection: 'row',
    alignItems: 'center',
    borderColor: Colors.border,
    borderRadius: 20,
    borderWidth: 1,
    paddingHorizontal: 12,
    paddingVertical: 6,
    gap: 6,
    maxWidth: '50%',
  },
  citySelectorText: {
    color: Colors.textPrimary,
    fontFamily: 'Cairo-Bold',
    fontSize: 13,
  },
  citySelectorIcon: {
    fontSize: 14,
  },
  citySelectorChevron: {
    color: Colors.textSecondary,
    fontSize: 10,
  },
  searchSection: {
    paddingHorizontal: 20,
    paddingVertical: 16,
  },
  searchBox: {
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 18,
    borderWidth: 1,
    flexDirection: 'row-reverse',
    gap: 8,
    padding: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 2,
  },
  searchInput: {
    color: Colors.textPrimary,
    flex: 1,
    fontFamily: 'Cairo-Regular',
    fontSize: 14,
    paddingHorizontal: 10,
    paddingVertical: 8,
  },
  searchBtn: {
    backgroundColor: Colors.primary,
    borderRadius: 14,
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  searchBtnIcon: {
    color: Colors.white,
    fontSize: 16,
  },
  section: {
    marginTop: 12,
  },
  providersList: {
    paddingHorizontal: 20,
    gap: 12,
  },
  sectionHeader: {
    alignItems: 'center',
    flexDirection: 'row-reverse',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    marginBottom: 10,
    marginTop: 16,
  },
  sectionTitle: {
    color: Colors.navy,
    fontFamily: 'Cairo-Bold',
    fontSize: 18,
  },
  sectionAction: {
    color: Colors.primary,
    fontFamily: 'Cairo-SemiBold',
    fontSize: 13,
  },
  categoryRail: {
    gap: 8,
    paddingHorizontal: 20,
    paddingBottom: 12,
  },
  categoryChip: {
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: 16,
    paddingVertical: 9,
  },
  categoryText: {
    color: Colors.textPrimary,
    fontFamily: 'Cairo-SemiBold',
    fontSize: 13,
  },
  statsSection: {
    marginTop: 24,
    paddingHorizontal: 20,
  },
  statsKicker: {
    color: Colors.primary,
    fontFamily: 'Cairo-Bold',
    fontSize: 12,
    textAlign: 'right',
  },
  statsTitle: {
    color: Colors.navy,
    fontFamily: 'Cairo-Black',
    fontSize: 20,
    textAlign: 'right',
    marginTop: 4,
    marginBottom: 16,
  },
  statsGrid: {
    gap: 12,
  },
  statsRow: {
    flexDirection: 'row-reverse',
    gap: 12,
  },
  statCard: {
    flex: 1,
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 18,
    borderWidth: 1,
    padding: 14,
    flexDirection: 'row-reverse',
    alignItems: 'center',
    justifyContent: 'space-between',
    minHeight: 76,
  },
  statContent: {
    alignItems: 'flex-end',
  },
  statValue: {
    color: Colors.navy,
    fontFamily: 'Cairo-Black',
    fontSize: 18,
  },
  statLabel: {
    color: Colors.textSecondary,
    fontFamily: 'Cairo-Regular',
    fontSize: 12,
    marginTop: 2,
  },
  statIconContainer: {
    backgroundColor: '#FFF7ED',
    borderRadius: 12,
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
  },
  statIconText: {
    color: Colors.primary,
    fontSize: 18,
  },
  lpCta: {
    backgroundColor: Colors.navy,
    borderRadius: 24,
    marginHorizontal: 20,
    marginTop: 28,
    padding: 24,
    alignItems: 'center',
    gap: 16,
  },
  lpCtaTextContainer: {
    alignItems: 'center',
  },
  lpCtaKicker: {
    color: '#FED7AA',
    fontFamily: 'Cairo-Bold',
    fontSize: 14,
  },
  lpCtaTitle: {
    color: Colors.white,
    fontFamily: 'Cairo-Black',
    fontSize: 20,
    marginTop: 4,
    textAlign: 'center',
  },
  lpCtaBtn: {
    backgroundColor: Colors.primary,
    borderRadius: 14,
    paddingHorizontal: 24,
    paddingVertical: 12,
    width: '100%',
    alignItems: 'center',
  },
  lpCtaBtnText: {
    color: Colors.white,
    fontFamily: 'Cairo-Bold',
    fontSize: 14,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(11, 26, 52, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: Colors.white,
    borderTopLeftRadius: 28,
    borderTopRightRadius: 28,
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: Platform.OS === 'ios' ? 40 : 24,
    maxHeight: '70%',
  },
  modalTitle: {
    color: Colors.navy,
    fontFamily: 'Cairo-Black',
    fontSize: 18,
    textAlign: 'center',
    marginBottom: 16,
  },
  cityList: {
    marginBottom: 16,
  },
  cityItem: {
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    alignItems: 'center',
  },
  cityItemActive: {
    backgroundColor: '#FFF7ED',
    borderRadius: 12,
    borderBottomWidth: 0,
  },
  cityItemText: {
    color: Colors.textPrimary,
    fontFamily: 'Cairo-SemiBold',
    fontSize: 15,
  },
  cityItemTextActive: {
    color: Colors.primary,
  },
  modalCloseBtn: {
    backgroundColor: Colors.background,
    borderRadius: 14,
    paddingVertical: 12,
    alignItems: 'center',
  },
  modalCloseBtnText: {
    color: Colors.textSecondary,
    fontFamily: 'Cairo-Bold',
    fontSize: 14,
  },
});
