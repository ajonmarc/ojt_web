<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
} from 'chart.js';
import { Line, Bar, Pie } from 'vue-chartjs';
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
);

const props = defineProps({
    applicationStats: Object,
    partnerStats: Object,
    studentStats: Object,
    monthlyApplications: Array,
    topPartners: Array,
    completionRates: Array,
    reportData: {
        type: Array,
        default: () => []
    },
    flash: Object
});

const selectedReportType = ref('applications');
const selectedDateRange = ref('month');
const selectedStatus = ref('');
const isLoading = ref(false);

// Chart Data
const applicationChartData = computed(() => ({
    labels: ['Total', 'Pending', 'Approved', 'Rejected'],
    datasets: [{
        label: 'Applications',
        data: [
            props.applicationStats.total,
            props.applicationStats.pending,
            props.applicationStats.approved,
            props.applicationStats.rejected
        ],
        backgroundColor: ['#2196F3', '#FFC107', '#4CAF50', '#F44336'],
    }]
}));

const monthlyApplicationsData = computed(() => ({
    labels: props.monthlyApplications.map(item => item.month),
    datasets: [{
        label: 'Monthly Applications',
        data: props.monthlyApplications.map(item => item.count),
        borderColor: '#2196F3',
        tension: 0.1,
        fill: false
    }]
}));

const completionRatesData = computed(() => ({
    labels: props.completionRates.map(item => item.completion_range),
    datasets: [{
        data: props.completionRates.map(item => item.count),
        backgroundColor: ['#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107'],
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: {
            position: 'bottom'
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
};

const generateReport = () => {
    isLoading.value = true;
    router.post(route('reports.generate'), {
        reportType: selectedReportType.value,
        dateRange: selectedDateRange.value,
        status: selectedStatus.value
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            if (props.flash?.success) {
                Swal.fire({
                    title: 'Success!',
                    text: props.flash.success,
                    icon: 'success',
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            }
        },
        onError: (errors) => {
            isLoading.value = false;
            console.error('Error generating report:', errors);
            Swal.fire({
                title: 'Error!',
                text: errors?.error || 'Failed to generate report. Please try again.',
                icon: 'error',
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        }
    });
};

const downloadReport = () => {
    if (!props.reportData.length) return;
    
    const csvContent = generateCSV(props.reportData);
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${selectedReportType.value}_report_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
};

const generateCSV = (data) => {
    if (!data.length) return '';
    
    const headers = Object.keys(data[0]);
    const csvRows = [headers.join(',')];
    
    for (const row of data) {
        const values = headers.map(header => {
            const value = row[header];
            return typeof value === 'string' ? `"${value}"` : value;
        });
        csvRows.push(values.join(','));
    }
    
    return csvRows.join('\n');
};

const formatHeader = (header) => {
    return header
        .replace(/_/g, ' ')
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const formatValue = (value) => {
    if (value === null || value === undefined) return '-';
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (value instanceof Date) return value.toLocaleDateString();
    return value;
};

const getPendingPercentage = computed(() => {
    const total = props.applicationStats.total;
    return total ? Math.round((props.applicationStats.pending / total) * 100) : 0;
});

const getApprovedPercentage = computed(() => {
    const total = props.applicationStats.total;
    return total ? Math.round((props.applicationStats.approved / total) * 100) : 0;
});

const getRejectedPercentage = computed(() => {
    const total = props.applicationStats.total;
    return total ? Math.round((props.applicationStats.rejected / total) * 100) : 0;
});

const getActivePartnersPercentage = computed(() => {
    const total = props.partnerStats.total;
    return total ? Math.round((props.partnerStats.active / total) * 100) : 0;
});

const getInactivePartnersPercentage = computed(() => {
    const total = props.partnerStats.total;
    return total ? Math.round((props.partnerStats.inactive / total) * 100) : 0;
});

const getOnOJTPercentage = computed(() => {
    const total = props.studentStats.total;
    return total ? Math.round((props.studentStats.onOJT / total) * 100) : 0;
});

const getCompletedPercentage = computed(() => {
    const total = props.studentStats.total;
    return total ? Math.round((props.studentStats.completed / total) * 100) : 0;
});
</script>

<template>
    <AdminLayout>
        <Head>
            <title>Reports - OJT Management System</title>
        </Head>

        <div class="reports-container">
            <h2>Reports</h2>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <!-- Application Stats -->
                <div class="stats-card">
                    <div class="stats-header">
                        <h3>Applications</h3>
                        <div class="stats-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="stats-content">
                        <div class="stat-row">
                            <div class="stat-item">
                                <span class="stat-label">Total</span>
                                <span class="stat-value total">{{ props.applicationStats.total }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Pending</span>
                                <span class="stat-value pending">{{ props.applicationStats.pending }}</span>
                            </div>
                        </div>
                        <div class="stat-row">
                            <div class="stat-item">
                                <span class="stat-label">Approved</span>
                                <span class="stat-value approved">{{ props.applicationStats.approved }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Rejected</span>
                                <span class="stat-value rejected">{{ props.applicationStats.rejected }}</span>
                            </div>
                        </div>
                        <div class="stats-progress">
                            <div class="progress-bar">
                                <div class="progress-segment pending" :style="{ width: getPendingPercentage + '%' }" :title="'Pending: ' + getPendingPercentage + '%'"></div>
                                <div class="progress-segment approved" :style="{ width: getApprovedPercentage + '%' }" :title="'Approved: ' + getApprovedPercentage + '%'"></div>
                                <div class="progress-segment rejected" :style="{ width: getRejectedPercentage + '%' }" :title="'Rejected: ' + getRejectedPercentage + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Partner Stats -->
                <div class="stats-card">
                    <div class="stats-header">
                        <h3>Partners</h3>
                        <div class="stats-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="stats-content">
                        <div class="stat-row">
                            <div class="stat-item">
                                <span class="stat-label">Total</span>
                                <span class="stat-value total">{{ props.partnerStats.total }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Active</span>
                                <span class="stat-value approved">{{ props.partnerStats.active }}</span>
                            </div>
                        </div>
                        <div class="stat-row">
                            <div class="stat-item full-width">
                                <span class="stat-label">Inactive</span>
                                <span class="stat-value rejected">{{ props.partnerStats.inactive }}</span>
                            </div>
                        </div>
                        <div class="stats-progress">
                            <div class="progress-bar">
                                <div class="progress-segment approved" :style="{ width: getActivePartnersPercentage + '%' }" :title="'Active: ' + getActivePartnersPercentage + '%'"></div>
                                <div class="progress-segment rejected" :style="{ width: getInactivePartnersPercentage + '%' }" :title="'Inactive: ' + getInactivePartnersPercentage + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Stats -->
                <div class="stats-card">
                    <div class="stats-header">
                        <h3>Students</h3>
                        <div class="stats-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="stats-content">
                        <div class="stat-row">
                            <div class="stat-item">
                                <span class="stat-label">Total</span>
                                <span class="stat-value total">{{ props.studentStats.total }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">On OJT</span>
                                <span class="stat-value pending">{{ props.studentStats.onOJT }}</span>
                            </div>
                        </div>
                        <div class="stat-row">
                            <div class="stat-item full-width">
                                <span class="stat-label">Completed</span>
                                <span class="stat-value approved">{{ props.studentStats.completed }}</span>
                            </div>
                        </div>
                        <div class="stats-progress">
                            <div class="progress-bar">
                                <div class="progress-segment pending" :style="{ width: getOnOJTPercentage + '%' }" :title="'On OJT: ' + getOnOJTPercentage + '%'"></div>
                                <div class="progress-segment approved" :style="{ width: getCompletedPercentage + '%' }" :title="'Completed: ' + getCompletedPercentage + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Application Status Distribution</h3>
                    <div class="chart-container">
                        <Bar 
                            :data="applicationChartData"
                            :options="chartOptions"
                        />
                    </div>
                </div>

                <div class="chart-card">
                    <h3>Monthly Applications Trend</h3>
                    <div class="chart-container">
                        <Line 
                            :data="monthlyApplicationsData"
                            :options="chartOptions"
                        />
                    </div>
                </div>

                <div class="chart-card">
                    <h3>OJT Completion Rates</h3>
                    <div class="chart-container">
                        <Pie 
                            :data="completionRatesData"
                            :options="chartOptions"
                        />
                    </div>
                </div>
            </div>

            <!-- Report Generator -->
            <div class="report-generator">
                <h3>Generate Report</h3>
                <div class="filters">
                    <div class="filter-group">
                        <label>Report Type</label>
                        <select v-model="selectedReportType">
                            <option value="applications">Applications</option>
                            <option value="partners">Partners</option>
                            <option value="students">Students</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Date Range</label>
                        <select v-model="selectedDateRange">
                            <option value="week">Last Week</option>
                            <option value="month">Last Month</option>
                            <option value="quarter">Last Quarter</option>
                            <option value="year">Last Year</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>

                    <div class="filter-group" v-if="selectedReportType === 'applications'">
                        <label>Status</label>
                        <select v-model="selectedStatus">
                            <option value="">All</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="actions">
                    <button @click="generateReport" :disabled="isLoading" class="generate-btn">
                        <span v-if="isLoading">Generating...</span>
                        <span v-else>Generate Report</span>
                    </button>
                </div>
            </div>

            <div v-if="props.reportData?.length > 0" class="report-results mt-4">
                <div class="report-header">
                    <h4>Report Results</h4>
                    <button @click="downloadReport" class="download-btn">
                        Download CSV
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th v-for="header in Object.keys(props.reportData[0])" :key="header">
                                    {{ formatHeader(header) }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in props.reportData" :key="index">
                                <td v-for="header in Object.keys(props.reportData[0])" :key="header">
                                    {{ formatValue(row[header]) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.reports-container {
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.stats-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.stats-header h3 {
    font-size: 1.25rem;
    color: #2c3e50;
    margin: 0;
}

.stats-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-icon i {
    font-size: 1.25rem;
    color: #2196F3;
}

.stats-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-row {
    display: flex;
    gap: 1rem;
}

.stat-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stat-item.full-width {
    flex: 2;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
}

.stat-value.total { color: #2196F3; }
.stat-value.pending { color: #FFC107; }
.stat-value.approved { color: #4CAF50; }
.stat-value.rejected { color: #F44336; }

.stats-progress {
    margin-top: 1rem;
}

.progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    display: flex;
    overflow: hidden;
}

.progress-segment {
    height: 100%;
    transition: width 0.3s ease;
}

.progress-segment.pending { background-color: #FFC107; }
.progress-segment.approved { background-color: #4CAF50; }
.progress-segment.rejected { background-color: #F44336; }

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.chart-card h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.report-generator {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.filters {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    color: #666;
}

.filter-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.generate-btn {
    background-color: #2196F3;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    min-width: 150px;
}

.generate-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.report-results {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.download-btn {
    background-color: #4CAF50;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.table-responsive {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.report-table th,
.report-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.report-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.report-table tr:hover {
    background-color: #f9f9f9;
}
</style>