import { router } from 'expo-router';
import { useEffect, useState } from 'react';
import { FlatList, Pressable, StyleSheet, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { EmptyState, LoadingState } from '../../components/ScreenState';
import { Colors } from '../../constants/colors';
import api from '../../lib/api';
import { unwrap } from '../../lib/apiResponse';
import type { ApiEnvelope, NamedResource } from '../../types/api';

export default function CategoriesScreen() {
  const [categories, setCategories] = useState<NamedResource[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get<ApiEnvelope<NamedResource[]>>('/categories')
      .then(({ data }) => setCategories(unwrap(data)))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingState />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Text style={styles.heading}>التصنيفات</Text>
      <FlatList
        data={categories}
        numColumns={2}
        keyExtractor={(item) => String(item.id)}
        contentContainerStyle={styles.grid}
        columnWrapperStyle={styles.row}
        ListEmptyComponent={<EmptyState title="لا توجد تصنيفات متاحة حاليا." />}
        renderItem={({ item }) => (
          <Pressable style={styles.card} onPress={() => router.push(`/category/${item.slug}`)}>
            <Text style={styles.cardText}>{item.name}</Text>
            <Text style={styles.cardHint}>استعراض المزودين</Text>
          </Pressable>
        )}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  heading: { color: Colors.navy, fontFamily: 'Cairo-Black', fontSize: 24, padding: 20, textAlign: 'right' },
  grid: { paddingHorizontal: 16, paddingBottom: 24 },
  row: { flexDirection: 'row-reverse', gap: 12, marginBottom: 12 },
  card: {
    alignItems: 'flex-end',
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 18,
    borderWidth: 1,
    flex: 1,
    minHeight: 116,
    padding: 18,
    justifyContent: 'space-between',
  },
  cardText: { color: Colors.textPrimary, fontFamily: 'Cairo-Bold', fontSize: 16, textAlign: 'right' },
  cardHint: { color: Colors.primary, fontFamily: 'Cairo-SemiBold', fontSize: 12, textAlign: 'right' },
});
