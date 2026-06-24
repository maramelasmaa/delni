import { router } from 'expo-router';
import { Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { Colors } from '../constants/colors';
import type { ProviderCard as ProviderCardType } from '../types/api';

export function ProviderCard({ provider }: { provider: ProviderCardType }) {
  const rating = Number(provider.rating_average ?? 0);
  const reviews = Number(provider.reviews_count ?? 0);

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={`عرض مزود الخدمة ${provider.name}`}
      style={styles.card}
      onPress={() => router.push(`/provider/${provider.slug}`)}
    >
      {provider.logo_url ? (
        <Image source={{ uri: provider.logo_url }} style={styles.logo} />
      ) : (
        <View style={[styles.logo, styles.placeholder]}>
          <Text style={styles.initial}>{provider.name?.[0] ?? 'د'}</Text>
        </View>
      )}

      <View style={styles.body}>
        <View style={styles.titleRow}>
          {provider.is_featured ? <Text style={styles.badge}>مميز</Text> : null}
          <Text style={styles.name} numberOfLines={1}>{provider.name}</Text>
        </View>
        {provider.category ? <Text style={styles.category}>{provider.category.name}</Text> : null}
        {provider.city ? <Text style={styles.meta}>📍 {provider.city.name}</Text> : null}
        {reviews > 0 ? <Text style={styles.rating}>★ {rating.toFixed(1)} · {reviews} تقييم</Text> : null}
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    flexDirection: 'row-reverse',
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 18,
    borderWidth: 1,
    marginHorizontal: 20,
    marginBottom: 12,
    padding: 14,
    shadowColor: '#0B1A34',
    shadowOpacity: 0.06,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 6 },
    elevation: 2,
  },
  logo: { width: 58, height: 58, borderRadius: 16, marginStart: 12 },
  placeholder: { alignItems: 'center', backgroundColor: '#FFF3EC', justifyContent: 'center' },
  initial: { color: Colors.primary, fontFamily: 'Cairo-Black', fontSize: 24 },
  body: { flex: 1, alignItems: 'flex-end' },
  titleRow: { flexDirection: 'row-reverse', alignItems: 'center', gap: 8 },
  name: { color: Colors.textPrimary, flex: 1, fontFamily: 'Cairo-Bold', fontSize: 16, textAlign: 'right' },
  badge: { backgroundColor: '#FEF3C7', borderRadius: 10, color: '#92400E', fontFamily: 'Cairo-Bold', fontSize: 11, paddingHorizontal: 8, paddingVertical: 2 },
  category: { color: Colors.primary, fontFamily: 'Cairo-SemiBold', fontSize: 13, marginTop: 3 },
  meta: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 12, marginTop: 2 },
  rating: { color: Colors.star, fontFamily: 'Cairo-SemiBold', fontSize: 12, marginTop: 5 },
});
