import { router } from 'expo-router';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Colors } from '../constants/colors';

export default function PrivacyScreen() {
  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backText}>→</Text>
        </Pressable>
        <Text style={styles.headerTitle}>سياسة الخصوصية</Text>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>من نحن</Text>
          <Text style={styles.paragraph}>
            دلني منصة دليل إلكتروني تهدف إلى مساعدة المستخدمين في العثور على مقدمي الخدمات داخل ليبيا والتواصل معهم. ولا تقوم المنصة بتقديم الخدمات بنفسها، وإنما توفر وسيلة لعرض معلومات مقدمي الخدمات وتسهيل الوصول إليهم.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>المعلومات التي نقوم بجمعها</Text>
          <Text style={styles.paragraph}>
            • الاسم ورقم الهاتف والبريد الإلكتروني عند إنشاء حساب أو التواصل معنا.{"\n"}
            • بيانات مقدمي الخدمات، مثل اسم النشاط، المدينة، الفئة، الوصف، الصور ووسائل التواصل.{"\n"}
            • التقييمات والمراجعات والمحتوى الذي يقدمه المستخدمون.{"\n"}
            • بيانات الاستخدام الأساسية مثل عمليات البحث بهدف تحسين أداء المنصة وتجربة المستخدم.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>كيفية استخدام المعلومات</Text>
          <Text style={styles.paragraph}>
            • عرض بيانات مقدمي الخدمات داخل المنصة.{"\n"}
            • تحسين تجربة البحث والتصفح وتطوير خدمات المنصة.{"\n"}
            • إدارة الحسابات والاشتراكات والمحتوى المنشور.{"\n"}
            • مراجعة البلاغات والحد من إساءة استخدام المنصة.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>مشاركة المعلومات</Text>
          <Text style={styles.paragraph}>
            لا تقوم دلني ببيع البيانات الشخصية للمستخدمين. وقد يتم عرض بعض المعلومات الخاصة بمقدمي الخدمات بشكل علني، باعتبار أن ذلك يمثل الغرض الأساسي من المنصة.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>التواصل خارج المنصة</Text>
          <Text style={styles.paragraph}>
            أي تواصل يتم عبر الهاتف أو تطبيق واتساب أو وسائل التواصل الأخرى يتم خارج نطاق منصة دلني، ولا تتحمل المنصة مسؤولية تلك التعاملات.
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
