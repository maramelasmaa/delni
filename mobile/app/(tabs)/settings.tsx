import { router } from 'expo-router';
import { useState } from 'react';
import { Alert, Linking, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Colors } from '../../constants/colors';
import { WEB_URL } from '../../constants/api';
import { useAuth } from '../../hooks/useAuth';

export default function SettingsScreen() {
  const { isAuthenticated, user, signIn, register, requestPasswordReset, signOut, deleteAccount } = useAuth();
  const [mode, setMode] = useState<'login' | 'register' | 'forgot_password'>('login');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [busy, setBusy] = useState(false);

  const confirmDeleteAccount = () => {
    Alert.alert(
      'تأكيد الحذف',
      'هل أنت متأكد من رغبتك في حذف حسابك نهائياً؟ لا يمكن التراجع عن هذا الإجراء.',
      [
        { text: 'إلغاء', style: 'cancel' },
        {
          text: 'حذف',
          style: 'destructive',
          onPress: async () => {
            setBusy(true);
            try {
              await deleteAccount();
              Alert.alert('تم', 'تم حذف حسابك بنجاح.');
            } catch (error) {
              Alert.alert('تنبيه', error instanceof Error ? error.message : 'تعذر حذف الحساب.');
            } finally {
              setBusy(false);
            }
          },
        },
      ]
    );
  };


  const submit = async () => {
    setBusy(true);
    try {
      if (mode === 'login') {
        await signIn(email.trim(), password);
      } else {
        await register(name.trim(), email.trim(), password, passwordConfirmation);
      }
      setPassword('');
      setPasswordConfirmation('');
    } catch (error) {
      Alert.alert('تنبيه', error instanceof Error ? error.message : 'حدث خطأ غير متوقع.');
    } finally {
      setBusy(false);
    }
  };

  const resetPassword = async () => {
    if (!email.trim()) {
      Alert.alert('تنبيه', 'اكتب بريدك الإلكتروني أولا.');
      return;
    }

    setBusy(true);
    try {
      const message = await requestPasswordReset(email.trim());
      Alert.alert('تم', message);
      setMode('login');
    } catch (error) {
      Alert.alert('تنبيه', error instanceof Error ? error.message : 'تعذر إرسال رابط إعادة التعيين.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={styles.heading}>الحساب والدعم</Text>

        {isAuthenticated && user ? (
          <View style={styles.card}>
            <Text style={styles.profileName}>{user.name}</Text>
            <Text style={styles.profileEmail}>{user.email}</Text>
            <Pressable style={styles.logoutBtn} onPress={signOut} disabled={busy}>
              <Text style={styles.logoutText}>تسجيل الخروج</Text>
            </Pressable>
            <Pressable style={styles.deleteBtn} onPress={confirmDeleteAccount} disabled={busy}>
              <Text style={styles.deleteText}>حذف الحساب</Text>
            </Pressable>
          </View>
        ) : (
          <View style={styles.card}>
            {mode !== 'forgot_password' ? (
              <View style={styles.modeRow}>
                <Pressable style={[styles.modeBtn, mode === 'login' && styles.modeActive]} onPress={() => setMode('login')}>
                  <Text style={[styles.modeText, mode === 'login' && styles.modeTextActive]}>دخول</Text>
                </Pressable>
                <Pressable style={[styles.modeBtn, mode === 'register' && styles.modeActive]} onPress={() => setMode('register')}>
                  <Text style={[styles.modeText, mode === 'register' && styles.modeTextActive]}>حساب جديد</Text>
                </Pressable>
              </View>
            ) : (
              <Text style={styles.modeTextTitle}>استعادة كلمة المرور</Text>
            )}

            {mode === 'register' ? (
              <View style={styles.inputContainer}>
                <Text style={styles.inputLabel}>الاسم</Text>
                <TextInput
                  style={styles.input}
                  value={name}
                  onChangeText={setName}
                  placeholder="الاسم"
                  placeholderTextColor={Colors.textMuted}
                  textAlign="right"
                  autoCapitalize="words"
                />
              </View>
            ) : null}
            <View style={styles.inputContainer}>
              <Text style={styles.inputLabel}>البريد الإلكتروني</Text>
              <TextInput
                style={styles.input}
                value={email}
                onChangeText={setEmail}
                placeholder="البريد الإلكتروني"
                placeholderTextColor={Colors.textMuted}
                keyboardType="email-address"
                autoCapitalize="none"
                textAlign="right"
              />
            </View>
            {mode !== 'forgot_password' ? (
              <View style={styles.inputContainer}>
                <Text style={styles.inputLabel}>كلمة المرور</Text>
                <TextInput
                  style={styles.input}
                  value={password}
                  onChangeText={setPassword}
                  placeholder="كلمة المرور"
                  placeholderTextColor={Colors.textMuted}
                  secureTextEntry
                  textAlign="right"
                />
              </View>
            ) : null}
            {mode === 'register' ? (
              <View style={styles.inputContainer}>
                <Text style={styles.inputLabel}>تأكيد كلمة المرور</Text>
                <TextInput
                  style={styles.input}
                  value={passwordConfirmation}
                  onChangeText={setPasswordConfirmation}
                  placeholder="تأكيد كلمة المرور"
                  placeholderTextColor={Colors.textMuted}
                  secureTextEntry
                  textAlign="right"
                />
              </View>
            ) : null}

            {mode === 'forgot_password' ? (
              <Pressable style={[styles.primaryBtn, busy && styles.disabled]} onPress={resetPassword} disabled={busy}>
                <Text style={styles.primaryText}>إرسال رابط التعيين</Text>
              </Pressable>
            ) : (
              <Pressable style={[styles.primaryBtn, busy && styles.disabled]} onPress={submit} disabled={busy}>
                <Text style={styles.primaryText}>{mode === 'login' ? 'تسجيل الدخول' : 'إنشاء الحساب'}</Text>
              </Pressable>
            )}

            {mode === 'login' ? (
              <Pressable onPress={() => setMode('forgot_password')} disabled={busy}>
                <Text style={styles.forgot}>نسيت كلمة المرور؟</Text>
              </Pressable>
            ) : mode === 'forgot_password' ? (
              <Pressable onPress={() => setMode('login')} disabled={busy}>
                <Text style={styles.forgot}>العودة لتسجيل الدخول</Text>
              </Pressable>
            ) : null}
          </View>
        )}

        <View style={styles.section}>
          <SettingsRow label="من نحن" onPress={() => router.push('/about')} />
          <SettingsRow label="تواصل معنا" onPress={() => router.push('/contact')} />
          <SettingsRow label="سياسة الخصوصية" onPress={() => router.push('/privacy')} />
          <SettingsRow label="الشروط والأحكام" onPress={() => router.push('/terms')} />
          <SettingsRow label="إخلاء المسؤولية" onPress={() => router.push('/disclaimer')} />
        </View>

        <Text style={styles.reviewNote}>
          لحماية المستخدمين، يمكنك الإبلاغ عن التقييمات المسيئة من صفحة مزود الخدمة، ويتم التعامل معها من فريق الإدارة.
        </Text>
      </ScrollView>
    </SafeAreaView>
  );
}

function SettingsRow({ label, onPress }: { label: string; onPress: () => void }) {
  return (
    <Pressable style={styles.row} onPress={onPress} accessibilityRole="button">
      <Text style={styles.rowChevron}>‹</Text>
      <Text style={styles.rowLabel}>{label}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  content: { paddingBottom: 32 },
  heading: { color: Colors.navy, fontFamily: 'Cairo-Black', fontSize: 24, padding: 20, textAlign: 'right' },
  card: {
    alignItems: 'stretch',
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 18,
    borderWidth: 1,
    gap: 12,
    marginHorizontal: 20,
    marginBottom: 20,
    padding: 18,
  },
  profileName: { color: Colors.textPrimary, fontFamily: 'Cairo-Bold', fontSize: 18, textAlign: 'right' },
  profileEmail: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 13, textAlign: 'right' },
  modeRow: { backgroundColor: Colors.background, borderRadius: 14, flexDirection: 'row-reverse', padding: 4 },
  modeBtn: { flex: 1, borderRadius: 12, paddingVertical: 10 },
  modeActive: { backgroundColor: Colors.white },
  modeText: { color: Colors.textSecondary, fontFamily: 'Cairo-Bold', textAlign: 'center' },
  modeTextActive: { color: Colors.primary },
  inputContainer: {
    gap: 6,
  },
  inputLabel: {
    color: Colors.textSecondary,
    fontFamily: 'Cairo-Bold',
    fontSize: 13,
    textAlign: 'right',
  },
  input: {
    backgroundColor: Colors.background,
    borderColor: Colors.border,
    borderRadius: 14,
    borderWidth: 1,
    color: Colors.textPrimary,
    fontFamily: 'Cairo-Regular',
    fontSize: 15,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  primaryBtn: { alignItems: 'center', backgroundColor: Colors.primary, borderRadius: 14, paddingVertical: 13 },
  disabled: { opacity: 0.6 },
  primaryText: { color: Colors.white, fontFamily: 'Cairo-Bold', fontSize: 15 },
  forgot: { color: Colors.primary, fontFamily: 'Cairo-SemiBold', fontSize: 13, textAlign: 'center' },
  section: {
    backgroundColor: Colors.white,
    borderColor: Colors.border,
    borderRadius: 18,
    borderWidth: 1,
    marginHorizontal: 20,
    overflow: 'hidden',
  },
  row: {
    alignItems: 'center',
    borderBottomColor: Colors.border,
    borderBottomWidth: 1,
    flexDirection: 'row',
    gap: 12,
    paddingHorizontal: 18,
    paddingVertical: 16,
  },
  rowLabel: { color: Colors.textPrimary, flex: 1, fontFamily: 'Cairo-SemiBold', fontSize: 15, textAlign: 'right' },
  rowChevron: { color: Colors.textMuted, fontSize: 22 },
  logoutBtn: { alignItems: 'center', backgroundColor: '#FEE2E2', borderRadius: 14, paddingVertical: 12 },
  logoutText: { color: Colors.error, fontFamily: 'Cairo-Bold', fontSize: 14 },
  deleteBtn: { alignItems: 'center', backgroundColor: '#FFF5F5', borderRadius: 14, paddingVertical: 12, marginTop: 8, borderColor: '#FEE2E2', borderWidth: 1 },
  deleteText: { color: Colors.error, fontFamily: 'Cairo-Bold', fontSize: 14 },
  modeTextTitle: { color: Colors.primary, fontFamily: 'Cairo-Bold', fontSize: 16, textAlign: 'center', marginVertical: 4 },
  reviewNote: { color: Colors.textSecondary, fontFamily: 'Cairo-Regular', fontSize: 13, lineHeight: 22, margin: 20, textAlign: 'right' },
});
