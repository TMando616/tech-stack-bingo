import { ref } from 'vue'
import { api } from '../api/axios'

export interface GlobalTrend {
  label: string;
  count: number;
}

export interface UserPerformance {
  user_total_bingos: number;
  average_bingos: number;
  max_bingos: number;
  rank_info: {
    rank: number;
    total_users: number;
  };
}

export interface Recommendation {
  label: string;
  user_count: number;
}

export interface AnalyticsSummary {
  global_trends: GlobalTrend[];
  user_performance: UserPerformance;
  recommendations: Recommendation[];
}

export function useAnalytics() {
  const summary = ref<AnalyticsSummary | null>(null)
  const isLoading = ref(false)

  const fetchSummary = async () => {
    try {
      isLoading.value = true
      const response = await api.get('/analytics/summary')
      summary.value = response.data
      return true
    } catch (error) {
      console.error('統計データの取得失敗:', error)
      return false
    } finally {
      isLoading.value = false
    }
  }

  return {
    summary,
    isLoading,
    fetchSummary
  }
}
