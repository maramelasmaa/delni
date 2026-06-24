import { router } from 'expo-router';
import { useEffect, useState } from 'react';
import { Alert, Linking, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LoadingState } from '../components/ScreenState';
import { Colors } from '../constants/colors';
import api from '../lib/api';
import { unwrap } from '../lib/apiResponse';
import type { ApiEnvelope } from '../types/api';

type ContactData = {
  whatsapp: string | null;
  phone: string | null;
  email: string | null;
  facebook: string | null;
  address: string | null;
};

export default function ContactScreen() {
  const [contact, setContact] = useState<ContactData | null>(null);
  const [loading, setLoading] = useState(true);
  const [openFaq, setOpenFaq] = useState<number | null>(null);

  useEffect(() => {
    api.get<ApiEnvelope<ContactData>>('/contact')
      .then(({ data }) => setContact(unwrap(data)))
      .catch(() => Alert.alert('تنبيه', 'تعذر تحميل معلومات الاتصال.'))
      .finally(() => setLoading(false));
  }, []);

  const toggleFaq = (index: number) => {
    setOpenFaq(openFaq === index ? null : index);
  };

  const openWhatsApp = (num: string) => {
    const cleanNum = num.replace(/[^0-9]/g, '');
    Linking.openURL(`https://wa.me/${cleanNum}`);
  };

  if (loading) return <LoadingState />;

  const faqs = [
    {
      q: 'كيف يمكنني استخدام التطبيق؟',
      a: 'يمكنك استخدام تطبيق دلني للبحث وتصفح التخصصات المختلفة للعثور على مقدمي الخدمات المحليين الموثوقين في مدينتك والتواصل معهم مباشرة عبر الواتساب أو الهاتف دون أي وسطاء أو رسوم إضافية.',
    },
    {
      q: 'كيف يمكنني التسجيل كمقدم خدمة في دلني؟',
      a: 'التسجيل في غاية السهولة! كل ما عليك فعله هو الضغط على زر الواتساب أعلاه، وسيقوم فريق الدعم الفني بمساعدتك في إعداد ملفك الشخصي وتنشيط اشتراكك للظهور في نتائج البحث.',
    },
    {
      q: 'هل يقدم دلني الدعم الفني مجاناً للمستخدمين؟',
      a: 'نعم بالتأكيد! نحن نقدم الدعم الفني الكامل والمساعدة للمستخدمين والعملاء لتسهيل الوصول لمقدمي الخدمات المناسبين مجاناً ودون أي رسوم.',
    },
  ];

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backText}>→</Text>
        </Pressable>
        <Text style={styles.headerTitle}>تواصل معنا</Text>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Text style={styles.sectionTitle}>وسائل التواصل المباشر</Text>
        <View style={styles.list}>
          {contact?.whatsapp ? (
            <Pressable style={styles.item} onPress={() => openWhatsApp(contact.whatsapp!)}>
              <View style={styles.itemBadge}>
                <Text style={styles.icon}>💬</Text>
              </View>
              <View style={styles.itemContent}>
                <Text style={styles.itemTitle}>واتساب</Text>
                <Text style={styles.itemSubtitle}>تواصل فوري ومحادثة مباشرة</Text>
              </View>
              <Text style={styles.arrow}>‹</Text>
            </Pressable>
          ) : null}

          {contact?.phone ? (
            <Pressable style={styles.item} onPress={() => Linking.openURL(`tel:${contact.phone}`)}>
              <View style={styles.itemBadge}>
                <Text style={styles.icon}>📞</Text>
              </View>
              <View style={styles.itemContent}>
                <Text style={styles.itemTitle}>اتصال هاتفي</Text>
                <Text style={styles.itemSubtitle}>اتصال هاتفي مباشر</Text>
              </View>
              <Text style={styles.arrow}>‹</Text>
            </Pressable>
          ) : null}

          {contact?.email ? (
            <Pressable style={styles.item} onPress={() => Linking.openURL(`mailto:${contact.email}`)}>
              <View style={styles.itemBadge}>
                <Text style={styles.icon}>✉️</Text>
              </View>
              <View style={styles.itemContent}>
                <Text style={styles.itemTitle}>البريد الإلكتروني</Text>
                <Text style={styles.itemSubtitle}>راسل فريق الدعم الفني</Text>
              </View>
              <Text style={styles.arrow}>‹</Text>
            </Pressable>
          ) : null}

          {contact?.facebook ? (
            <Pressable style={styles.item} onPress={() => Linking.openURL(contact.facebook!)}>
              <View style={styles.itemBadge}>
                <Text style={styles.icon}>🌐</Text>
              </View>
              <View style={styles.itemContent}>
                <Text style={styles.itemTitle}>فيسبوك</Text>
                <Text style={styles.itemSubtitle}>تابع صفحتنا وتواصل معنا</Text>
              </View>
              <Text style={styles.arrow}>‹</Text>
            </Pressable>
          ) : null}
        </View>

        <Text style={[styles.sectionTitle, { marginTop: 24 }]}>الأسئلة الشائعة</Text>
        <View style={styles.faqList}>
          {faqs.map((faq, index) => (
            <View key={index} style={styles.faqItem}>
              <Pressable style={styles.faqHeader} onPress={() => toggleFaq(index)}>
                <Text style={styles.faqChevron}>{openFaq === index ? '▲' : '▼'}</Text>
                <Text style={styles.faqQuestion}>{faq.q}</Text>
              </Pressable>
              {openFaq === index ? (
                <View style={styles.faqAnswerContainer}>
                  <Text style={styles.faqAnswer}>{faq.a}</Text>
                </View>
              ) : null}
            </View>
          ))}
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
  sectionTitle: { color: Colors.primary, fontFamily: 'Cairo-Bold', fontSize: 14, marginBottom: 10, textAlign: 'right' },
  list: { gap: 10 },
  item: { alignItems: 'center', backgroundColor: Colors.white, borderColor: Colors.border, borderRadius: 20, borderWidth: 1, flexDirection: 'row-reverse', gap: 12, padding: 14 },
  itemBadge: { alignItems: 'center', backgroundColor: '#FFF3EC', borderRadius: 14, height: 44, justifyContent: 'center', width: 44 },
  icon: { fontSize: 20 },
  itemContent: { flex: 1, alignItems: 'flex-end' },
  itemTitle: { color: Colors.textPrimary, fontFamily: 'Cairo-Bold', fontSize: 15 },
  itemSubtitle: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 12, marginTop: 2 },
  arrow: { color: Colors.textMuted, fontSize: 18, paddingLeft: 4 },
  faqList: { gap: 8 },
  faqItem: { backgroundColor: Colors.white, borderColor: Colors.border, borderRadius: 18, borderWidth: 1, overflow: 'hidden' },
  faqHeader: { alignItems: 'center', flexDirection: 'row-reverse', justifyContent: 'space-between', paddingHorizontal: 16, paddingVertical: 14 },
  faqQuestion: { color: Colors.textPrimary, fontFamily: 'Cairo-Bold', fontSize: 13, textAlign: 'right', flex: 1, paddingLeft: 8 },
  faqChevron: { color: Colors.textMuted, fontSize: 10 },
  faqAnswerContainer: { borderTopColor: Colors.border, borderTopWidth: 1, padding: 16, backgroundColor: Colors.background },
  faqAnswer: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 12, lineHeight: 20, textAlign: 'right' },
});
