<script setup lang="ts">
import { onMounted } from 'vue'
import { useAnalytics } from '../composables/useAnalytics'

const props = defineProps<{
  show: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const { summary, isLoading, fetchSummary } = useAnalytics()

onMounted(() => {
  if (props.show) {
    fetchSummary()
  }
})
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="emit('close')">
    <div class="modal-content">
      <header class="modal-header">
        <h3>📊 統計ダッシュボード</h3>
        <button class="btn-close" @click="emit('close')">&times;</button>
      </header>

      <div v-if="isLoading" class="loading">データを取得中...</div>
      
      <div v-else-if="summary" class="dashboard-body">
        <!-- ユーザーパフォーマンス -->
        <section class="stat-section">
          <h4>あなたの進捗</h4>
          <div class="perf-grid">
            <div class="perf-card">
              <span class="label">累計ビンゴ数</span>
              <span class="value">{{ summary.user_performance.user_total_bingos }}</span>
            </div>
            <div class="perf-card">
              <span class="label">全体平均</span>
              <span class="value">{{ summary.user_performance.average_bingos }}</span>
            </div>
            <div class="perf-card highlight">
              <span class="label">全体順位</span>
              <span class="value">{{ summary.user_performance.rank_info.rank }} / {{ summary.user_performance.rank_info.total_users }} 位</span>
            </div>
          </div>
        </section>

        <!-- グローバルトレンド -->
        <section class="stat-section">
          <h4>人気の技術トレンド</h4>
          <div class="trend-list">
            <div v-for="(trend, index) in summary.global_trends" :key="trend.label" class="trend-item">
              <span class="trend-rank">{{ index + 1 }}</span>
              <span class="trend-label">{{ trend.label }}</span>
              <div class="trend-bar-bg">
                <div class="trend-bar" :style="{ width: (trend.count / summary.global_trends[0].count * 100) + '%' }"></div>
              </div>
              <span class="trend-count">{{ trend.count }} 名</span>
            </div>
          </div>
        </section>

        <!-- レコメンド -->
        <section class="stat-section recommendations">
          <h4>💡 次に習得すべき技術 (おすすめ)</h4>
          <div class="rec-tags">
            <span v-for="rec in summary.recommendations" :key="rec.label" class="rec-tag">
              {{ rec.label }}
            </span>
          </div>
          <p class="rec-note">※他のユーザーが多く達成している技術から算出しています</p>
        </section>
      </div>

      <div v-else class="error">データの読み込みに失敗しました。</div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1100; backdrop-filter: blur(2px); }
.modal-content { background: white; padding: 2rem; border-radius: 12px; width: 95%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; }

.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
.modal-header h3 { margin: 0; font-size: 1.5rem; color: #2c3e50; }
.btn-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; }

.loading, .error { padding: 3rem; text-align: center; color: #666; }

.stat-section { margin-bottom: 2rem; }
.stat-section h4 { margin-bottom: 1rem; color: #34495e; border-left: 4px solid #3498db; padding-left: 10px; }

/* 進捗カード */
.perf-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.perf-card { background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center; border: 1px solid #edf2f7; }
.perf-card .label { display: block; font-size: 0.75rem; color: #718096; margin-bottom: 0.5rem; }
.perf-card .value { font-size: 1.25rem; font-weight: bold; color: #2d3748; }
.perf-card.highlight { background: #ebf8ff; border-color: #bee3f8; }
.perf-card.highlight .value { color: #3182ce; }

/* トレンドリスト */
.trend-list { display: flex; flex-direction: column; gap: 0.8rem; }
.trend-item { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
.trend-rank { width: 20px; font-weight: bold; color: #a0aec0; }
.trend-label { width: 100px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.trend-bar-bg { flex: 1; height: 12px; background: #edf2f7; border-radius: 6px; overflow: hidden; }
.trend-bar { height: 100%; background: linear-gradient(90deg, #3498db, #2ecc71); border-radius: 6px; transition: width 1s ease-out; }
.trend-count { width: 50px; text-align: right; font-size: 0.8rem; color: #718096; }

/* レコメンド */
.rec-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
.rec-tag { background: #edf2f7; color: #4a5568; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0; }
.rec-note { font-size: 0.75rem; color: #a0aec0; font-style: italic; }

/* ダークモード対応 */
:global(body.dark-mode) .modal-content { background: #1e1e1e; color: #e0e0e0; }
:global(body.dark-mode) .modal-header h3, 
:global(body.dark-mode) .stat-section h4 { color: #fff; }
:global(body.dark-mode) .perf-card { background: #2d2d2d; border-color: #3d3d3d; }
:global(body.dark-mode) .perf-card .value { color: #e0e0e0; }
:global(body.dark-mode) .perf-card.highlight { background: #1a365d; border-color: #2a4365; }
:global(body.dark-mode) .perf-card.highlight .value { color: #63b3ed; }
:global(body.dark-mode) .trend-bar-bg { background: #3d3d3d; }
:global(body.dark-mode) .rec-tag { background: #2d2d2d; color: #e0e0e0; border-color: #3d3d3d; }
</style>
