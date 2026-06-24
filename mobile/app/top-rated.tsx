import { router } from 'expo-router';
import { useEffect, useState } from 'react';
import { FlatList, Pressable, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ProviderCard } from '../components/ProviderCard';
import { EmptyState, LoadingState } from '../components/ScreenState';
import { Colors } from '../constants/colors';
import api from '../lib/api';
import { unwrap } from '../lib/apiResponse';
import type { ApiEnvelope, ProviderCard as ProviderCardType } from '../types/api';

export default function TopRatedScreen() {
  const [providers, setProviders] = useState<ProviderCardType[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get<ApiEnvelope<ProviderCardType[]>>('/top-rated')
      .then(({ data }) => setProviders(unwrap(data)))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingState />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.topBar}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.back}>→</Text>
        </Pressable>
        <Text style={styles.heading}>الأعلى تقييما</Text>
      </View>
      <FlatList
        data={providers}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => <ProviderCard provider={item} />}
        contentContainerStyle={styles.list}
        ListEmptyComponent={<EmptyState title="لا توجد نتائج أعلى تقييما حاليا." />}
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
  list: { flexGrow: 1, paddingVertical: 12 },
});
