import * as SecureStore from 'expo-secure-store';
import React, { createContext, useCallback, useEffect, useState } from 'react';
import type { ApiEnvelope, User } from '../types/api';
import { messageFromError, unwrap } from './apiResponse';
import api, { TOKEN_KEY } from './api';

type AuthState =
  | { status: 'loading' }
  | { status: 'unauthenticated' }
  | { status: 'authenticated'; user: User };

type AuthContextType = {
  authState: AuthState;
  signIn: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
  requestPasswordReset: (email: string) => Promise<string>;
  signOut: () => Promise<void>;
  deleteAccount: () => Promise<void>;
  isAuthenticated: boolean;
  user: User | null;
};

export const AuthContext = createContext<AuthContextType | null>(null);

type AuthPayload = {
  token: string;
  user: User;
};

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [authState, setAuthState] = useState<AuthState>({ status: 'loading' });

  useEffect(() => {
    restoreSession();
  }, []);

  const persistAuth = async (payload: AuthPayload) => {
    await SecureStore.setItemAsync(TOKEN_KEY, payload.token);
    setAuthState({ status: 'authenticated', user: payload.user });
  };

  const restoreSession = async () => {
    try {
      const token = await SecureStore.getItemAsync(TOKEN_KEY);
      if (!token) {
        setAuthState({ status: 'unauthenticated' });
        return;
      }

      const response = await api.get<ApiEnvelope<User>>('/auth/me');
      setAuthState({ status: 'authenticated', user: unwrap(response.data) });
    } catch {
      await SecureStore.deleteItemAsync(TOKEN_KEY);
      setAuthState({ status: 'unauthenticated' });
    }
  };

  const signIn = useCallback(async (email: string, password: string) => {
    try {
      const response = await api.post<ApiEnvelope<AuthPayload>>('/auth/login', { email, password });
      await persistAuth(unwrap(response.data));
    } catch (error) {
      throw new Error(messageFromError(error, 'تعذر تسجيل الدخول. تحقق من البريد وكلمة المرور.'));
    }
  }, []);

  const register = useCallback(async (name: string, email: string, password: string, passwordConfirmation: string) => {
    try {
      const response = await api.post<ApiEnvelope<AuthPayload>>('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      await persistAuth(unwrap(response.data));
    } catch (error) {
      throw new Error(messageFromError(error, 'تعذر إنشاء الحساب. تحقق من البيانات وحاول مجددا.'));
    }
  }, []);

  const requestPasswordReset = useCallback(async (email: string) => {
    try {
      const response = await api.post<ApiEnvelope<unknown>>('/auth/forgot-password', { email });
      return response.data.message ?? 'إذا كان البريد مسجلا لدينا فسيصلك رابط إعادة التعيين.';
    } catch (error) {
      throw new Error(messageFromError(error, 'تعذر إرسال رابط إعادة التعيين حاليا.'));
    }
  }, []);

  const signOut = useCallback(async () => {
    try {
      await api.post('/auth/logout');
    } catch {
      // Local token removal is still the important client-side logout step.
    }
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    setAuthState({ status: 'unauthenticated' });
  }, []);

  const deleteAccount = useCallback(async () => {
    try {
      await api.delete('/auth/account');
    } catch {
      // Local token removal is still the important client-side step.
    }
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    setAuthState({ status: 'unauthenticated' });
  }, []);

  const isAuthenticated = authState.status === 'authenticated';
  const user = authState.status === 'authenticated' ? authState.user : null;

  return (
    <AuthContext.Provider value={{ authState, signIn, register, requestPasswordReset, signOut, deleteAccount, isAuthenticated, user }}>
      {children}
    </AuthContext.Provider>
  );
}
