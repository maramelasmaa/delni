export type ApiEnvelope<T> = {
  success: boolean;
  message?: string;
  data: T;
  pagination?: Pagination;
};

export type Pagination = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  has_more: boolean;
};

export type NamedResource = {
  id: number;
  slug: string;
  name: string;
  name_ar?: string;
  icon_url?: string | null;
};

export type ProviderCard = {
  id: number;
  slug: string;
  name: string;
  category: NamedResource | null;
  subcategories?: NamedResource[];
  city: NamedResource | null;
  rating_average: number;
  reviews_count: number;
  logo_url: string | null;
  cover_url: string | null;
  is_featured?: boolean;
  whatsapp_url: string | null;
  phone: string | null;
};

export type HomePayload = {
  stats: {
    visible_providers_count: number;
    categories_count: number;
    cities_count: number;
    reviews_count: number;
  };
  categories: NamedResource[];
  featured_providers: ProviderCard[];
  suggested_providers: ProviderCard[];
};

export type ProviderDetail = ProviderCard & {
  description: string | null;
  portfolio_images: string[];
  portfolio_items: Array<{
    id: number;
    title: string;
    description: string | null;
    images: string[];
  }>;
  website: string | null;
  social_links: Record<string, string | null>;
  service_area_note: string | null;
  years_experience: number | null;
  is_favorited: boolean;
  can_review: boolean;
  review_status_message: string | null;
  reviews: Review[];
};

export type Review = {
  id: number;
  rating: number | null;
  comment: string | null;
  user_name: string;
  created_at: string;
};

export type Favorite = {
  id: number;
  created_at: string | null;
  provider: ProviderCard;
};

export type User = {
  id: number;
  name: string;
  email: string;
};
