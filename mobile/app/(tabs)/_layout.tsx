import { Tabs } from 'expo-router';
import { Platform, Text } from 'react-native';
import { Colors } from '../../constants/colors';

export default function TabsLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: Colors.primary,
        tabBarInactiveTintColor: Colors.textMuted,
        tabBarStyle: {
          backgroundColor: Colors.white,
          borderTopColor: Colors.border,
          height: Platform.OS === 'ios' ? 88 : 64,
          paddingBottom: Platform.OS === 'ios' ? 28 : 10,
          paddingTop: 8,
        },
        tabBarLabelStyle: {
          fontFamily: 'Cairo-SemiBold',
          fontSize: 11,
        },
      }}
    >
      <Tabs.Screen name="index" options={{ title: 'الرئيسية', tabBarIcon: ({ color }) => <TabIcon glyph="⌂" color={String(color)} /> }} />
      <Tabs.Screen name="categories" options={{ title: 'التصنيفات', tabBarIcon: ({ color }) => <TabIcon glyph="▦" color={String(color)} /> }} />
      <Tabs.Screen name="favorites" options={{ title: 'المفضلة', tabBarIcon: ({ color }) => <TabIcon glyph="♡" color={String(color)} /> }} />
      <Tabs.Screen name="settings" options={{ title: 'الحساب', tabBarIcon: ({ color }) => <TabIcon glyph="●" color={String(color)} /> }} />
    </Tabs>
  );
}

function TabIcon({ glyph, color }: { glyph: string; color: string }) {
  return <Text style={{ color, fontSize: 20, lineHeight: 20 }}>{glyph}</Text>;
}
