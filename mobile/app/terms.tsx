import { router } from 'expo-router';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Colors } from '../constants/colors';

export default function TermsScreen() {
  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backText}>→</Text>
        </Pressable>
        <Text style={styles.headerTitle}>شروط الاستخدام</Text>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>قبول الشروط</Text>
          <Text style={styles.paragraph}>
            يُعد استخدام منصة دلني موافقةً من المستخدم على الالتزام بهذه الشروط والأحكام.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>طبيعة المنصة</Text>
          <Text style={styles.paragraph}>
            دلني منصة دليل إلكتروني، ولا تُعد طرفاً في أي اتفاق أو تعاقد أو تعامل يتم بين المستخدم ومقدم الخدمة.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>مسؤوليات المستخدم</Text>
          <Text style={styles.paragraph}>
            • استخدام المنصة بطريقة قانونية ومسؤولة.{"\n"}
            • الامتناع عن تقديم بلاغات أو تقييمات مضللة.{"\n"}
            • عدم القيام بأي تصرف يضر بالمنصة أو يعطل عملها.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>مسؤوليات مقدم الخدمة</Text>
          <Text style={styles.paragraph}>
            • صحة ودقة المعلومات التي يقدمها.{"\n"}
            • جودة الخدمات التي يقدمها.{"\n"}
            • جميع التعاملات والاتفاقات التي تتم مع العملاء.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>المحتوى المحظور</Text>
          <Text style={styles.paragraph}>
            • مخالف للقوانين أو الأنظمة المعمول بها.{"\n"}
            • مضلل أو غير صحيح.{"\n"}
            • ينتهك حقوق الآخرين أو يمس بسمعتهم.{"\n"}
            • يتضمن إساءة أو ألفاظاً غير لائقة.
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>التعاملات خارج المنصة</Text>
          <Text style={styles.paragraph}>
            تتم جميع الاتفاقات والمدفوعات والتعاملات التي تتم خارج منصة دلني على مسؤولية الأطراف المعنية وحدهم، ولا تتحمل المنصة أي مسؤولية عنها.
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
