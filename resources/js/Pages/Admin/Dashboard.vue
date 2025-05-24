<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    stats: Object,
    recentStudents: Array,
    programStats: Array,
    users: Array
});

const showProgramStats = ref(false);

const getProgramStudentCount = (programName) => {
    return props.users.filter(user => 
        user.role === 'student' && user.ojtProgram === programName
    ).length;
};

const getProgramActiveStudentCount = (programName) => {
    return props.users.filter(user => 
        user.role === 'student' && 
        user.ojtProgram === programName && 
        user.status === 'Active'
    ).length;
};

const calculateProgramProgress = (programName) => {
    const totalStudents = getProgramStudentCount(programName);
    const activeStudents = getProgramActiveStudentCount(programName);
    return totalStudents === 0 ? 0 : (activeStudents / totalStudents) * 100;
};

const getProgressColorClass = (percentage) => {
    if (percentage < 33) return 'low';
    if (percentage < 66) return 'medium';
    return 'high';
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="dashboard-container">
            <h2 class="dashboard-title">Dashboard</h2>
            
            <!-- Statistics Cards -->
            <div class="card-grid">
                <div class="card">
                    <div class="icon">👥</div>
                    <div class="total-number">{{ stats.totalStudents }}</div>
                    <h3>Total Students</h3>
                    <Link :href="route('admin.student')" class="view-btn">View All</Link>
                </div>
                <div class="card">
                    <div class="icon">📋</div>
                    <div class="total-number">{{ stats.totalPrograms }}</div>
                    <h3>OJT Programs</h3>
                    <Link :href="route('admin.program')" class="view-btn">View All</Link>
                </div>
                <div class="card">
                    <div class="icon">✅</div>
                    <div class="total-number">{{ stats.activeStudents }}</div>
                    <h3>Active Students</h3>
                    <Link :href="route('admin.student')" class="view-btn">View All</Link>
                </div>
                <div class="card">
                    <div class="icon">⏳</div>
                    <div class="total-number">{{ stats.pendingApplications }}</div>
                    <h3>Pending Applications</h3>
                    <Link :href="route('admin.application')" class="view-btn">View All</Link>
                </div>
            </div>

            <!-- Recent Students and Program Statistics -->
            <div class="dashboard-grid">
                <!-- Recent Students -->
                <div class="dashboard-section">
                    <h3>Recent Students</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="student in recentStudents" :key="student.id">
                                    <td>{{ student.name }}</td>
                                    <td>{{ student.ojtProgram }}</td>
                                    <td>
                                        <span :class="['status-badge', student.status]">
                                            {{ student.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Program Statistics Dropdown -->
                <div class="program-stats-dropdown">
                    <button @click="showProgramStats = !showProgramStats" class="program-stats-btn">
                        <span>Program Statistics</span>
                        <i :class="['fas', showProgramStats ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                    </button>
                    
                    <div v-if="showProgramStats" class="program-stats-content">
                        <div class="program-stats-grid">
                            <div v-for="program in programStats" :key="program.id" class="program-stat-card">
                                <div class="program-info">
                                    <h3>{{ program.programName }}</h3>
                                    <div class="program-numbers">
                                        <div class="stat-item">
                                            <span class="label">Total Students:</span>
                                            <span class="value">{{ getProgramStudentCount(program.programName) }}</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="label">Active:</span>
                                            <span class="value">{{ getProgramActiveStudentCount(program.programName) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" 
                                         :style="{ width: calculateProgramProgress(program.programName) + '%' }"
                                         :class="getProgressColorClass(calculateProgramProgress(program.programName))">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.dashboard-container {
    padding: 20px;
}

.dashboard-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.total-number {
    font-size: 28px;
    font-weight: bold;
    color: #f7ce5a;
    margin-bottom: 5px;
}

.card h3 {
    color: #666;
    font-size: 16px;
    margin-bottom: 15px;
}

.view-btn {
    display: inline-block;
    background-color: #f7ce5a;
    color: #333;
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.2s;
}

.view-btn:hover {
    background-color: #f5c431;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.dashboard-section {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dashboard-section h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 18px;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background-color: #f8f9fa;
    color: #666;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.active {
    background-color: #e3fcef;
    color: #00875a;
}

.status-badge.pending {
    background-color: #fff7e6;
    color: #b76e00;
}

.program-stats-dropdown {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.program-stats-btn {
    width: 100%;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f7ce5a;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    transition: background-color 0.2s;
}

.program-stats-btn:hover {
    background: #f5c431;
}

.program-stats-content {
    padding: 20px;
    background: white;
}

.program-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.program-stat-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    transition: transform 0.2s;
}

.program-stat-card:hover {
    transform: translateY(-2px);
}

.program-info h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}

.program-numbers {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
}

.stat-item {
    display: flex;
    flex-direction: column;
}

.stat-item .label {
    font-size: 12px;
    color: #666;
}

.stat-item .value {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.progress-bar {
    height: 6px;
    background: #eee;
    border-radius: 3px;
    overflow: hidden;
}

.progress {
    height: 100%;
    transition: width 0.3s ease;
}

.progress.low {
    background: #ff4d4f;
}

.progress.medium {
    background: #faad14;
}

.progress.high {
    background: #52c41a;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    .program-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>