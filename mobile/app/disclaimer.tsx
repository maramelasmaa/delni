import { router } from 'expo-router';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Colors } from '../constants/colors';

export default function DisclaimerScreen() {
  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backText}>→</Text>
        </Pressable>
        <Text style={styles.headerTitle}>إخلاء المسؤولية</Text>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>طبيعة المنصة</Text>
          <Text style={styles.paragraph}>
            تُعد دلني منصة دليل إلكتروني مستقلة، ولا تعمل بصفتها وكيلاً أو ممثلاً لأي مقدم خدمة مدرج على المنصة.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>عدم ضمان جودة الخدمات</Text>
          <Text style={styles.paragraph}>
            لا تقدم دلني أي ضمانات تتعلق بجودة الخدمات أو سلامتها أو نتائجها، وتبقى المسؤولية الكاملة عن الخدمات المقدمة على عاتق مقدمي الخدمات.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>دقة المعلومات</Text>
          <Text style={styles.paragraph}>
            تبذل دلني جهوداً معقولة لضمان صحة المعلومات المنشورة وتحديثها، إلا أنها لا تضمن اكتمالها أو دقتها أو استمرار حداثتها.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>التواصل والمدفوعات خارج المنصة</Text>
          <Text style={styles.paragraph}>
            أي تواصل أو تفاوض أو دفع أو اتفاق يتم خارج منصة دلني يكون بين المستخدم ومقدم الخدمة مباشرة، ولا تتحمل المنصة أي مسؤولية عن تلك التعاملات.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>التقييمات والآراء</Text>
          <Text style={styles.paragraph}>
            تعبر التقييمات والتعليقات المنشورة عن آراء أصحابها، ولا تمثل بالضرورة رأي أو موقف منصة دلني.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>حدود المسؤولية</Text>
          <Text style={styles.paragraph}>
            في الحدود التي يسمح بها القانون، لا تتحمل دلني أي مسؤولية عن الأضرار أو الخسائر المباشرة أو غير المباشرة أو التبعية الناتجة عن استخدام المنصة أو عن التعامل مع مقدمي الخدمات المدرجين فيها.
          </Text>
        </View>

        <View style={styles.footer}>
          <Text style={styles.version}>آخر تحديث: 18/06/2026</Text>
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
  section: { backgroundColor: Colors.white, borderColor: Colors.border, borderRadius: 18, borderWidth: 1, marginBottom: 12, padding: 16 },
  sectionTitle: { color: Colors.primary, fontFamily: 'Cairo-Bold', fontSize: 16, marginBottom: 8, textAlign: 'right' },
  paragraph: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 13, lineHeight: 22, textAlign: 'right' },
  footer: { alignItems: 'center', marginTop: 20 },
  version: { color: Colors.textMuted, fontFamily: 'Cairo-Regular', fontSize: 12 },
});
