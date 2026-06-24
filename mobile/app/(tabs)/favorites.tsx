import { router } from 'expo-router';
import { useEffect, useState } from 'react';
import { FlatList, StyleSheet, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { ProviderCard } from '../../components/ProviderCard';
import { EmptyState, LoadingState } from '../../components/ScreenState';
import { Colors } from '../../constants/colors';
import { useAuth } from '../../hooks/useAuth';
import api from '../../lib/api';
import { unwrap } from '../../lib/apiResponse';
import type { ApiEnvelope, ProviderCard as ProviderCardType } from '../../types/api';

export default function FavoritesScreen() {
  const { isAuthenticated } = useAuth();
  const [favorites, setFavorites] = useState<ProviderCardType[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!isAuthenticated) {
      setLoading(false);
      setFavorites([]);
      return;
    }

    setLoading(true);
    api.get<ApiEnvelope<ProviderCardType[]>>('/favorites')
      .then(({ data }) => setFavorites(unwrap(data)))
      .finally(() => setLoading(false));
  }, [isAuthenticated]);

  if (loading) return <LoadingState />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Text style={styles.heading}>المفضلة</Text>
      {!isAuthenticated ? (
        <EmptyState title="سجل دخولك لحفظ مزودي الخدمة في المفضلة." actionLabel="فتح الحساب" onAction={() => router.push('/settings')} />
      ) : (
        <FlatList
          data={favorites}
          keyExtractor={(item) => String(item.id)}
          renderItem={({ item }) => <ProviderCard provider={item} />}
          contentContainerStyle={styles.list}
          ListEmptyComponent={<EmptyState title="لا توجد مفضلة بعد." actionLabel="تصفح المزودين" onAction={() => router.push('/')} />}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  heading: { color: Colors.navy, fontFamily: 'Cairo-Black', fontSize: 24, padding: 20, textAlign: 'right' },
  list: { flexGrow: 1, paddingVertical: 12 },
});
