import { router } from 'expo-router';
import { Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Colors } from '../constants/colors';

export default function AboutScreen() {
  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backText}>→</Text>
        </Pressable>
        <Text style={styles.headerTitle}>من نحن</Text>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.hero}>
          <Image source={require('../assets/icon.png')} style={styles.logo} />
          <Text style={styles.appName}>دلني</Text>
          <Text style={styles.tagline}>دليلك للعثور على أفضل مقدمي الخدمات</Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>ما هو دلني؟</Text>
          <Text style={styles.paragraph}>
            دلني منصة تربط العملاء بمقدمي الخدمات المحليين من مختلف التخصصات. سواء كنت تبحث عن سبّاك أو كاتب أو معلم خصوصي، دلني يساعدك في إيجاد الشخص المناسب بكل سهولة.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>رؤيتنا</Text>
          <Text style={styles.paragraph}>
            نسعى إلى بناء سوق محلي موثوق يُمكّن مقدمي الخدمات من الوصول إلى عملاء جدد، ويمنح العملاء خياراتٍ واسعةً وتقييماتٍ حقيقية.
          </Text>
        </View>

        <View style={styles.footer}>
          <Text style={styles.version}>الإصدار 1.0.0</Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  header: { alignItems: 'center', flexDirection: 'row-reverse', height: 56, justifyContent: 'space-between', paddingHorizontal: 16 },
  backBtn: { backgroundColor: Colors.white, borderRadius: 999, padding: 8 },
  backText: { color: Colors.navy, fontSize: 20 },
  headerTitle: { color: Colors.navy, fontFamily: 'Cairo-Bold', fontSize: 18 },
  content: { padding: 20, paddingBottom: 40 },
  hero: { alignItems: 'center', backgroundColor: Colors.navy, borderRadius: 24, padding: 24, marginBottom: 20 },
  logo: { borderRadius: 16, height: 72, width: 72 },
  appName: { color: Colors.white, fontFamily: 'Cairo-Black', fontSize: 22, marginTop: 10 },
  tagline: { color: '#CBD5E1', fontFamily: 'Cairo-Regular', fontSize: 13, marginTop: 4 },
  section: { backgroundColor: Colors.white, borderColor: Colors.border, borderRadius: 18, borderWidth: 1, marginBottom: 12, padding: 16 },
  sectionTitle: { color: Colors.primary, fontFamily: 'Cairo-Bold', fontSize: 16, marginBottom: 8, textAlign: 'right' },
  paragraph: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 13, lineHeight: 22, textAlign: 'right' },
  footer: { alignItems: 'center', marginTop: 20 },
  version: { color: Colors.textMuted, fontFamily: 'Cairo-Regular', fontSize: 12 },
});
