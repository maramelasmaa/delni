import { router, useLocalSearchParams } from 'expo-router';
import { useEffect, useState } from 'react';
import { FlatList, Pressable, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ProviderCard } from '../../components/ProviderCard';
import { EmptyState, LoadingState } from '../../components/ScreenState';
import { Colors } from '../../constants/colors';
import api from '../../lib/api';
import { unwrap } from '../../lib/apiResponse';
import type { ApiEnvelope, NamedResource, ProviderCard as ProviderCardType } from '../../types/api';

type CategoryPayload = {
  category: NamedResource;
  subcategories: NamedResource[];
  providers: ProviderCardType[];
};

export default function CategoryScreen() {
  const { slug } = useLocalSearchParams<{ slug: string }>();
  const [payload, setPayload] = useState<CategoryPayload | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get<ApiEnvelope<CategoryPayload>>(`/categories/${slug}`)
      .then(({ data }) => setPayload(unwrap(data)))
      .finally(() => setLoading(false));
  }, [slug]);

  if (loading) return <LoadingState />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.back}>→</Text>
        </Pressable>
        <Text style={styles.heading}>{payload?.category?.name ?? 'التصنيف'}</Text>
      </View>

      <FlatList
        data={payload?.providers ?? []}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => <ProviderCard provider={item} />}
        contentContainerStyle={styles.list}
        ListHeaderComponent={payload?.subcategories?.length ? (
          <View style={styles.chips}>
            {payload.subcategories.map((item) => (
              <Pressable key={item.id} style={styles.chip} onPress={() => router.push(`/search?subcategory=${item.slug}`)}>
                <Text style={styles.chipText}>{item.name}</Text>
              </Pressable>
            ))}
          </View>
        ) : null}
        ListEmptyComponent={<EmptyState title="لا يوجد مزودون ظاهرون في هذا التصنيف حاليا." />}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  topBar: { alignItems: 'center', flexDirection: 'row-reverse', gap: 12, padding: 20 },
  backBtn: { padding: 6 },
  back: { color: Colors.navy, fontSize: 22 },
  heading: { color: Colors.navy, flex: 1, fontFamily: 'Cairo-Black', fontSize: 22, textAlign: 'right' },
  list: { paddingBottom: 24 },
  chips: { flexDirection: 'row-reverse', flexWrap: 'wrap', gap: 8, paddingHorizontal: 20, paddingBottom: 12 },
  chip: { backgroundColor: '#FFF3EC', borderRadius: 999, paddingHorizontal: 12, paddingVertical: 7 },
  chipText: { color: Colors.primary, fontFamily: 'Cairo-SemiBold', fontSize: 12 },
});
