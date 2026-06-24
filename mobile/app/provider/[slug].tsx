import { router, useLocalSearchParams } from 'expo-router';
import type React from 'react';
import { useEffect, useState } from 'react';
import { Alert, Image, Linking, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { EmptyState, LoadingState } from '../../components/ScreenState';
import { Colors } from '../../constants/colors';
import { useAuth } from '../../hooks/useAuth';
import api from '../../lib/api';
import { messageFromError, unwrap } from '../../lib/apiResponse';
import type { ApiEnvelope, ProviderDetail, Review } from '../../types/api';

export default function ProviderScreen() {
  const { slug } = useLocalSearchParams<{ slug: string }>();
  const { isAuthenticated } = useAuth();
  const [provider, setProvider] = useState<ProviderDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [favorited, setFavorited] = useState(false);
  const [reviewComment, setReviewComment] = useState('');
  const [reviewRating, setReviewRating] = useState('5');
  const [reportingReviewId, setReportingReviewId] = useState<number | null>(null);
  const [reportReason, setReportReason] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const loadProvider = async () => {
    setLoading(true);
    try {
      const response = await api.get<ApiEnvelope<ProviderDetail>>(`/providers/${slug}`);
      const data = unwrap(response.data);
      setProvider(data);
      setFavorited(Boolean(data.is_favorited));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProvider();
  }, [slug]);

  const toggleFavorite = async () => {
    if (!provider) return;
    if (!isAuthenticated) {
      router.push('/settings');
      return;
    }

    const next = !favorited;
    setFavorited(next);
    try {
      if (next) {
        await api.post(`/favorites/${provider.slug}`);
      } else {
        await api.delete(`/favorites/${provider.slug}`);
      }
    } catch (error) {
      setFavorited(!next);
      Alert.alert('تنبيه', messageFromError(error, 'تعذر تحديث المفضلة.'));
    }
  };

  const submitReview = async () => {
    if (!provider) return;
    if (!isAuthenticated) {
      router.push('/settings');
      return;
    }

    setSubmitting(true);
    try {
      await api.post(`/providers/${provider.slug}/reviews`, {
        rating: reviewRating ? Number(reviewRating) : null,
        comment: reviewComment.trim() || null,
      });
      setReviewComment('');
      setReviewRating('5');
      Alert.alert('تم', 'تم إرسال تقييمك بنجاح.');
      await loadProvider();
    } catch (error) {
      Alert.alert('تنبيه', messageFromError(error, 'تعذر إرسال التقييم.'));
    } finally {
      setSubmitting(false);
    }
  };

  const reportReview = async (review: Review) => {
    if (!isAuthenticated) {
      router.push('/settings');
      return;
    }

    if (reportingReviewId !== review.id) {
      setReportingReviewId(review.id);
      setReportReason('');
      return;
    }

    if (reportReason.trim().length < 10) {
      Alert.alert('تنبيه', 'سبب البلاغ يجب أن يكون 10 أحرف على الأقل.');
      return;
    }

    try {
      await api.post(`/reviews/${review.id}/flag`, { reason: reportReason.trim() });
      Alert.alert('تم', 'تم إرسال البلاغ لفريق المراجعة.');
      setReportingReviewId(null);
      setReportReason('');
    } catch (error) {
      Alert.alert('تنبيه', messageFromError(error, 'تعذر إرسال البلاغ.'));
    }
  };

  if (loading) return <LoadingState />;
  if (!provider) return <EmptyState title="مزود الخدمة غير موجود أو غير متاح حاليا." actionLabel="رجوع" onAction={() => router.back()} />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.topBar}>
          <Pressable onPress={() => router.back()} style={styles.iconBtn}>
            <Text style={styles.iconText}>→</Text>
          </Pressable>
          <Pressable onPress={toggleFavorite} style={styles.iconBtn}>
            <Text style={[styles.heart, favorited && styles.heartActive]}>{favorited ? '♥' : '♡'}</Text>
          </Pressable>
        </View>

        {provider.cover_url ? <Image source={{ uri: provider.cover_url }} style={styles.cover} /> : null}

        <View style={styles.header}>
          {provider.logo_url ? <Image source={{ uri: provider.logo_url }} style={styles.logo} /> : (
            <View style={[styles.logo, styles.logoPlaceholder]}>
              <Text style={styles.initial}>{provider.name[0]}</Text>
            </View>
          )}
          <View style={styles.headerText}>
            <Text style={styles.name}>{provider.name}</Text>
            {provider.category ? <Text style={styles.category}>{provider.category.name}</Text> : null}
            {provider.city ? <Text style={styles.meta}>📍 {provider.city.name}</Text> : null}
            {provider.reviews_count > 0 ? <Text style={styles.rating}>★ {provider.rating_average.toFixed(1)} · {provider.reviews_count} تقييم</Text> : null}
          </View>
        </View>

        {provider.description ? <Section title="نبذة"><Text style={styles.paragraph}>{provider.description}</Text></Section> : null}

        {provider.subcategories?.length ? (
          <Section title="الخدمات">
            <View style={styles.chips}>
              {provider.subcategories.map((item) => <Text key={item.id} style={styles.chip}>{item.name}</Text>)}
            </View>
          </Section>
        ) : null}

        <Section title="التواصل">
          <View style={styles.actions}>
            {provider.whatsapp_url ? <Action label="واتساب" onPress={() => Linking.openURL(provider.whatsapp_url!)} /> : null}
            {provider.phone ? <Action label="اتصال" onPress={() => Linking.openURL(`tel:${provider.phone}`)} /> : null}
            {provider.website ? <Action label="الموقع" onPress={() => Linking.openURL(provider.website!)} /> : null}
          </View>
        </Section>

        {provider.portfolio_images?.length ? (
          <Section title="معرض الأعمال">
            <ScrollView horizontal showsHorizontalScrollIndicator={false}>
              {provider.portfolio_images.map((uri) => <Image key={uri} source={{ uri }} style={styles.portfolioImage} />)}
            </ScrollView>
          </Section>
        ) : null}

        <Section title="التقييمات">
          {provider.can_review ? (
            <View style={styles.reviewBox}>
              <TextInput style={styles.input} value={reviewRating} onChangeText={setReviewRating} keyboardType="number-pad" placeholder="التقييم من 1 إلى 5" textAlign="right" />
              <TextInput style={[styles.input, styles.textarea]} value={reviewComment} onChangeText={setReviewComment} placeholder="اكتب تجربتك باحترام ووضوح" multiline textAlign="right" />
              <Pressable style={[styles.primaryBtn, submitting && styles.disabled]} onPress={submitReview} disabled={submitting}>
                <Text style={styles.primaryText}>إرسال التقييم</Text>
              </Pressable>
            </View>
          ) : provider.review_status_message ? (
            <Text style={styles.note}>{provider.review_status_message}</Text>
          ) : null}

          {provider.reviews?.length ? provider.reviews.map((review) => (
            <View key={review.id} style={styles.reviewCard}>
              <Text style={styles.reviewName}>{review.user_name}</Text>
              {review.rating ? <Text style={styles.rating}>★ {review.rating}</Text> : null}
              {review.comment ? <Text style={styles.paragraph}>{review.comment}</Text> : null}
              {reportingReviewId === review.id ? (
                <TextInput
                  style={[styles.input, styles.reportInput]}
                  value={reportReason}
                  onChangeText={setReportReason}
                  placeholder="اكتب سبب البلاغ"
                  multiline
                  textAlign="right"
                />
              ) : null}
              <Pressable onPress={() => reportReview(review)}>
                <Text style={styles.report}>{reportingReviewId === review.id ? 'إرسال البلاغ' : 'الإبلاغ عن هذا التقييم'}</Text>
              </Pressable>
            </View>
          )) : <Text style={styles.note}>لا توجد تقييمات بعد.</Text>}
        </Section>
      </ScrollView>
    </SafeAreaView>
  );
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <View style={styles.section}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {children}
    </View>
  );
}

function Action({ label, onPress }: { label: string; onPress: () => void }) {
  return (
    <Pressable style={styles.actionBtn} onPress={onPress}>
      <Text style={styles.actionText}>{label}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  content: { paddingBottom: 32 },
  topBar: { flexDirection: 'row', justifyContent: 'space-between', padding: 16 },
  iconBtn: { backgroundColor: Colors.white, borderRadius: 999, padding: 10 },
  iconText: { color: Colors.navy, fontSize: 20 },
  heart: { color: Colors.textMuted, fontSize: 24 },
  heartActive: { color: Colors.error },
  cover: { height: 190, width: '100%' },
  header: { alignItems: 'flex-start', flexDirection: 'row-reverse', gap: 14, padding: 20 },
  logo: { borderRadius: 18, height: 78, width: 78 },
  logoPlaceholder: { alignItems: 'center', backgroundColor: '#FFF3EC', justifyContent: 'center' },
  initial: { color: Colors.primary, fontFamily: 'Cairo-Black', fontSize: 30 },
  headerText: { alignItems: 'flex-end', flex: 1 },
  name: { color: Colors.navy, fontFamily: 'Cairo-Black', fontSize: 24, textAlign: 'right' },
  category: { color: Colors.primary, fontFamily: 'Cairo-Bold', fontSize: 14, marginTop: 3 },
  meta: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 13, marginTop: 3 },
  rating: { color: Colors.star, fontFamily: 'Cairo-SemiBold', fontSize: 13, marginTop: 4 },
  section: { marginBottom: 18, paddingHorizontal: 20 },
  sectionTitle: { color: Colors.navy, fontFamily: 'Cairo-Bold', fontSize: 17, marginBottom: 9, textAlign: 'right' },
  paragraph: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 14, lineHeight: 23, textAlign: 'right' },
  chips: { flexDirection: 'row-reverse', flexWrap: 'wrap', gap: 8 },
  chip: { backgroundColor: '#FFF3EC', borderRadius: 999, color: Colors.primary, fontFamily: 'Cairo-SemiBold', paddingHorizontal: 12, paddingVertical: 6 },
  actions: { flexDirection: 'row-reverse', gap: 10 },
  actionBtn: { backgroundColor: Colors.navy, borderRadius: 14, flex: 1, paddingVertical: 12 },
  actionText: { color: Colors.white, fontFamily: 'Cairo-Bold', textAlign: 'center' },
  portfolioImage: { borderRadius: 16, height: 130, marginStart: 10, width: 180 },
  reviewBox: { backgroundColor: Colors.white, borderColor: Colors.border, borderRadius: 18, borderWidth: 1, gap: 10, marginBottom: 12, padding: 14 },
  input: { backgroundColor: Colors.background, borderColor: Colors.border, borderRadius: 14, borderWidth: 1, color: Colors.textPrimary, fontFamily: 'Cairo-Regular', padding: 12 },
  textarea: { minHeight: 90, textAlignVertical: 'top' },
  primaryBtn: { alignItems: 'center', backgroundColor: Colors.primary, borderRadius: 14, paddingVertical: 12 },
  disabled: { opacity: 0.6 },
  primaryText: { color: Colors.white, fontFamily: 'Cairo-Bold' },
  note: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', lineHeight: 22, textAlign: 'right' },
  reviewCard: { backgroundColor: Colors.white, borderColor: Colors.border, borderRadius: 16, borderWidth: 1, marginBottom: 10, padding: 14 },
  reviewName: { color: Colors.textPrimary, fontFamily: 'Cairo-Bold', textAlign: 'right' },
  report: { color: Colors.error, fontFamily: 'Cairo-SemiBold', fontSize: 12, marginTop: 8, textAlign: 'right' },
  reportInput: { marginTop: 10, minHeight: 72, textAlignVertical: 'top' },
});
