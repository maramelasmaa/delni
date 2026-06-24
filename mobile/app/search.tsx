import { router, useLocalSearchParams } from 'expo-router';
import { useEffect, useState } from 'react';
import { FlatList, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ProviderCard } from '../components/ProviderCard';
import { EmptyState, LoadingState } from '../components/ScreenState';
import { Colors } from '../constants/colors';
import api from '../lib/api';
import { unwrap } from '../lib/apiResponse';
import type { ApiEnvelope, ProviderCard as ProviderCardType } from '../types/api';

export default function SearchScreen() {
  const params = useLocalSearchParams<{ q?: string; subcategory?: string; service?: string; city?: string; category?: string }>();
  const [query, setQuery] = useState(params.q ?? '');
  const [results, setResults] = useState<ProviderCardType[]>([]);
  const [loading, setLoading] = useState(false);
  const [searched, setSearched] = useState(Boolean(params.q || params.subcategory));

  useEffect(() => {
    if (params.q || params.subcategory || params.service || params.city || params.category) {
      search(params.q ?? '', params.subcategory || params.service);
    }
  }, [params.q, params.subcategory, params.service, params.city, params.category]);

  const search = async (term: string, subcategory?: string) => {
    setLoading(true);
    setSearched(true);
    try {
      const response = await api.get<ApiEnvelope<ProviderCardType[]>>('/search', {
        params: {
          q: term.trim() || undefined,
          service: subcategory || params.service || undefined,
          city: params.city || undefined,
          category: params.category || undefined,
        },
      });
      setResults(unwrap(response.data));
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.back}>→</Text>
        </Pressable>
        <TextInput
          style={styles.input}
          value={query}
          onChangeText={setQuery}
          onSubmitEditing={() => search(query, params.subcategory)}
          placeholder="ابحث عن خدمة أو مزود"
          placeholderTextColor={Colors.textMuted}
          autoFocus
          returnKeyType="search"
          textAlign="right"
        />
      </View>

      {loading ? <LoadingState /> : (
        <FlatList
          data={results}
          keyExtractor={(item) => String(item.id)}
          renderItem={({ item }) => <ProviderCard provider={item} />}
          contentContainerStyle={styles.list}
          ListEmptyComponent={<EmptyState title={searched ? 'لا توجد نتائج مطابقة.' : 'ابدأ البحث عن مزود خدمة.'} />}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  topBar: { alignItems: 'center', flexDirection: 'row-reverse', gap: 10, padding: 16 },
  backBtn: { padding: 8 },
  back: { color: Colors.navy, fontSize: 20 },
  input: {
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 16,
    borderWidth: 1,
    color: Colors.textPrimary,
    flex: 1,
    fontFamily: 'Cairo-Regular',
    fontSize: 15,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  list: { flexGrow: 1, paddingVertical: 12 },
});
