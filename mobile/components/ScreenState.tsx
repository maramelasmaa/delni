import { ActivityIndicator, Pressable, StyleSheet, Text, View } from 'react-native';
import { Colors } from '../constants/colors';

export function LoadingState() {
  return (
    <View style={styles.centered}>
      <ActivityIndicator size="large" color={Colors.primary} />
    </View>
  );
}

export function EmptyState({ title, actionLabel, onAction }: { title: string; actionLabel?: string; onAction?: () => void }) {
  return (
    <View style={styles.centered}>
      <Text style={styles.title}>{title}</Text>
      {actionLabel && onAction ? (
        <Pressable style={styles.action} onPress={onAction}>
          <Text style={styles.actionText}>{actionLabel}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 24, gap: 14 },
  title: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 16, textAlign: 'center' },
  action: { backgroundColor: Colors.primary, borderRadius: 14, paddingHorizontal: 18, paddingVertical: 10 },
  actionText: { color: Colors.white, fontFamily: 'Cairo-Bold', fontSize: 14 },
});
