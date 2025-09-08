<template>
  <div class="user-graphs-outer">
    <div class="user-graphs-card">
      <div class="user-graphs-content">
        <div class="user-graph-section">
          <div class="user-graph-header">Original Self</div>
          <div class="user-graph-inner">
            <div class="user-graph-inner-header">
              <Dropdown
                mode="radio"
                :options="originalSelfOptions"
                v-model="originalSelf"
              />
            </div>
            <Bar
              :data="originalChartData"
              :options="chartOptions"
            />
          </div>
        </div>
        <div class="user-graph-section">
          <div class="user-graph-header">Adjusted Self</div>
          <div class="user-graph-inner">
            <div class="user-graph-inner-header">
              <Dropdown
                mode="radio"
                :options="adjustedSelfOptions"
                v-model="adjustedSelf"
              />
            </div>
            <Bar
              :data="adjustedChartData"
              :options="chartOptions"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Bar } from 'vue-chartjs';
import {
  Chart,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js';
import { ref } from 'vue';
import Dropdown from '@/components/Common/Common_UI/Dropdown.vue';

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const originalChartData = {
  labels: ['Info 1', 'Info 2', 'Info 3', 'Info 4', 'Info 5', 'Info 6'],
  datasets: [
    {
      label: 'Original Self',
      backgroundColor: '#0164A5',
      data: [40, 100, 30, 30, 10, 40],
      borderRadius: 0,
      barPercentage: 0.7,
      categoryPercentage: 0.7,
    },
  ],
};

const adjustedChartData = {
  labels: ['Info 1', 'Info 2', 'Info 3', 'Info 4', 'Info 5', 'Info 6'],
  datasets: [
    {
      label: 'Adjusted Self',
      backgroundColor: '#0164A5',
      data: [40, 60, 40, 10, 20, 30],
      borderRadius: 0,
      barPercentage: 0.7,
      categoryPercentage: 0.7,
    },
  ],
};

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { display: false },
    tooltip: { enabled: true },
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 100,
      ticks: { stepSize: 20 },
    },
  },
};

const originalSelfOptions = [
  { label: 'Original Self 1', value: 'Original Self 1' },
  { label: 'Original Self 2', value: 'Original Self 2' },
  { label: 'Original Self 3', value: 'Original Self 3' },
];
const adjustedSelfOptions = [
  { label: 'Adjusted Self 1', value: 'Adjusted Self 1' },
  { label: 'Adjusted Self 2', value: 'Adjusted Self 2' },
  { label: 'Adjusted Self 3', value: 'Adjusted Self 3' },
];
const originalSelf = ref('Original Self 1');
const adjustedSelf = ref('Adjusted Self 1');
</script>

<style scoped>
.user-graphs-outer {
  width: 100%;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
}

.user-graphs-card {
  width: 100%;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  box-sizing: border-box;
  min-width: 0;

  /* padding is handled by .user-graphs-content and .user-graph-section */
  display: flex;
  flex-direction: column;
  position: relative;
}

/* Responsive: shrink margin and padding on small screens */
@media (max-width: 1400px) {
  .user-graphs-outer {
    margin: 12px;
    max-width: 100%;
  }
  .user-graphs-card {
    border-radius: 14px;
    max-width: 100%;
  }
}

@media (max-width: 900px) {
  .user-graphs-outer {
    margin: 4px;
    max-width: 100%;
  }
  .user-graphs-card {
    border-radius: 10px;
  }
}

.user-graphs-content {
  display: flex;
  flex-direction: row;
  gap: 32px;
  justify-content: center;
  width: 100%;
  padding: 32px 0;
}
.user-graph-section {
  background: transparent;
  border-radius: 16px;
  box-shadow: none;
  padding: 32px 32px 32px 32px;
  min-width: 340px;
  flex: 1 1 0;
  display: flex;
  flex-direction: column;
  align-items: stretch;
}
.user-graph-header {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 18px;
  margin-left: 18px;
  text-align: left;
}
.user-graph-inner {
  background: #f8f8f8;
  border-radius: 16px;
  box-shadow: 0 1px 8px rgba(33, 150, 243, 0.08);
  padding: 24px 24px 24px 24px;
  min-width: 320px;
  display: flex;
  flex-direction: column;
  align-items: stretch;
}
.user-graph-inner-header {
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 8px;
  gap: 8px;
}
.user-graph-inner-header span {
  background: none;
  color: inherit;
  padding: 0;
  box-shadow: none;
  margin-right: 0;
}
.dropdown-radio {
  position: relative;
  display: inline-block;
  min-width: 140px;
  font-size: 15px;
  width: auto !important; /* override previous 100% */
  min-width: 0 !important;
  max-width: 100% !important;
}
.dropdown-radio-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #0164a5;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 6px 18px;
  font-weight: 500;
  cursor: pointer;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  box-shadow: 0 1px 4px rgba(33, 150, 243, 0.04);
  transition: background 0.2s;
}
.dropdown-radio-btn.open {
  background: #014a7c;
}
.dropdown-radio-arrow {
  width: 18px;
  height: 18px;
  margin-left: 8px;
  filter: grayscale(0.5);
}
.dropdown-radio-list {
  position: absolute;
  left: 0;
  top: 110%;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  z-index: 10;
  min-width: 140px;
  padding: 4px 0;
}
.dropdown-radio-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  background: none;
  border: none;
  padding: 8px 18px;
  font-size: 15px;
  color: #222;
  cursor: pointer;
  transition: background 0.15s;
}
.dropdown-radio-option:hover {
  background: #f0f0f0;
}
.dropdown-radio-circle {
  width: 12px;
  height: 12px;
  border: 2px solid #0164a5;
  border-radius: 50%;
  position: relative;
  flex-shrink: 0;
}
.dropdown-radio-circle.selected {
  background: #0164a5;
}
.graph-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: flex-end;
  align-items: flex-end;
  margin-bottom: 18px;
  width: 100%;
  box-sizing: border-box;
}
.graph-controls .dropdown-radio {
  flex: 1 1 180px;
  min-width: 140px;
  max-width: 100%;
  box-sizing: border-box;
}
@media (max-width: 1400px) {
  .user-graphs-content {
    gap: 18px;
    padding: 18px 0;
  }
  .user-graph-section {
    border-radius: 14px;
    min-width: 0;
    padding: 18px 8px 18px 8px;
  }
  .user-graph-inner {
    border-radius: 14px;
    padding: 18px 8px 18px 8px;
    min-width: 0;
  }
}
@media (max-width: 900px) {
  .user-graphs-content {
    flex-direction: column;
    gap: 12px;
    padding: 8px 0;
  }
  .user-graph-section {
    border-radius: 10px;
    min-width: 0;
    padding: 8px 4px 8px 4px;
  }
  .user-graph-inner {
    border-radius: 10px;
    padding: 8px 4px 8px 4px;
    min-width: 0;
  }
}
@media (max-width: 600px) {
  .graph-section {
    padding: 12px 4px 12px 4px;
  }
  .graph-controls {
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
    justify-content: flex-end;
  }
  .graph-controls .dropdown-radio {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    flex-basis: 100% !important;
  }
  .user-graph-inner-header {
    justify-content: flex-end;
    flex-direction: row;
    align-items: center;
  }
  .user-graph-inner-header .dropdown-radio {
    width: 240px !important;
    min-width: 240px !important;
    max-width: 240px !important;
  }
}

@container graph-section (max-width: 500px) {
  .graph-controls {
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
  }
  .graph-controls .dropdown-radio {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    flex-basis: 100% !important;
  }
}
</style>
