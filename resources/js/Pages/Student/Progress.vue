<template>
    <Head title="Student Progress" />

    <StudentLayout>
        <div class="progress-container">
            <div class="welcome-section">
                <h1>Welcome, {{ auth.user.name }}!</h1>
                <p class="student-id">Student ID: {{ auth.user.studentId }}</p>
            </div>

            <!-- Application Status -->
            <div class="card status-card" v-if="application">
                <h3>OJT Status</h3>
                <div class="status-content">
                    <div class="status-header">
                        <span :class="['status-badge', getStatusClass(application.status)]">
                            {{ application.status }}
                        </span>
                    </div>

                    <div v-if="application.status === 'Approved'" class="approved-details">
                        <!-- Company Info -->
                        <div class="company-info" v-if="application.partner">
                            <h4>Company Information</h4>
                            <div class="info-content">
                                <p><strong>Company:</strong> {{ partnerInfo.name }}</p>
                                <p><strong>Address:</strong> {{ partnerInfo.address }}</p>
                                <p><strong>Start Date:</strong> {{ formatDate(application.startDate) }}</p>
                                <p><strong>End Date:</strong> {{ formatDate(application.endDate) }}</p>
                            </div>
                        </div>

                        <!-- Progress Overview -->
                        <div class="progress-overview" v-if="application">
                            <h4>Progress Overview</h4>
                            <div class="progress-stats">
                                <div class="stat-item">
                                    <span class="label">Hours Completed</span>
                                    <span class="value">{{ application.completedHours || 0 }} / {{ application.requiredHours || 0 }}</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar">
                                        <div 
                                            class="progress-fill" 
                                            :style="{ width: `${progressPercentage}%` }"
                                        ></div>
                                    </div>
                                    <span class="percentage">{{ progressPercentage }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Progress Entries -->
                        <div class="recent-entries" v-if="progress?.recentEntries?.length">
                            <h4>Recent Progress</h4>
                            <div class="entries-list">
                                <div v-for="entry in progress.recentEntries" :key="entry.id" class="entry-item">
                                    <div class="entry-header">
                                        <span class="entry-date">{{ formatDate(entry.date) }}</span>
                                        <span class="entry-hours">{{ entry.hours }} hours</span>
                                    </div>
                                    <div class="entry-tasks">
                                        <strong>Tasks Completed:</strong>
                                        <p>{{ entry.tasks }}</p>
                                    </div>
                                    <div class="entry-remarks" v-if="entry.remarks">
                                        <strong>Remarks:</strong>
                                        <p>{{ entry.remarks }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="application.status === 'Rejected'" class="rejected-info">
                        <p class="rejection-message">Your application needs revision. Please check the remarks and reapply.</p>
                        <div v-if="application.remarks" class="remarks">
                            <h4>Remarks:</h4>
                            <p>{{ application.remarks }}</p>
                        </div>
                        <Link 
                            :href="route('student.application')"
                            class="reapply-btn"
                        >
                            Reapply
                        </Link>
                    </div>

                    <div v-else-if="application.status === 'Pending'" class="pending-info">
                        <p>Your application is under review. We will notify you once it's processed.</p>
                    </div>
                </div>
            </div>

            <!-- No Application Message -->
            <div class="card" v-else>
                <div class="no-application">
                    <p>You haven't submitted your OJT application yet.</p>
                    <Link :href="route('student.application')" class="apply-btn">
                        Apply Now
                    </Link>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

<script setup>
import StudentLayout from "@/Layouts/StudentLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from 'vue';

const props = defineProps({
    auth: Object,
    application: Object,
    progress: Object
});

// Safe computed properties for partner information
const partnerInfo = computed(() => ({
    name: props.application?.partner?.name ?? 'N/A',
    address: props.application?.partner?.address ?? 'N/A'
}));

const formatDate = (dateString) => {
    if (!dateString) return 'Not set';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch (error) {
        console.error('Invalid date format:', error);
        return 'Invalid date';
    }
};

const getStatusClass = (status) => {
    const classes = {
        'Pending': 'status-pending',
        'Approved': 'status-approved',
        'Rejected': 'status-rejected'
    };
    return classes[status] || '';
};

const calculateProgress = (completed, required) => {
    if (!required || required <= 0) return 0;
    return Math.min(100, Math.round((completed / required * 100)));
};

const progressPercentage = computed(() => {
    const completed = props.application?.completedHours || 0;
    const required = props.application?.requiredHours || 0;
    return calculateProgress(completed, required);
});
</script>

<style scoped>
.progress-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-section {
    margin-bottom: 30px;
}

.welcome-section h1 {
    font-size: 24px;
    color: #2c6929;
    margin: 0;
}

.student-id {
    color: #666;
    margin-top: 5px;
}

.card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card h3, .card h4 {
    color: #2c6929;
    margin-bottom: 15px;
}

.card h3 {
    font-size: 18px;
}

.card h4 {
    font-size: 16px;
    margin-top: 20px;
}

/* Status Styling */
.status-badge {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
}

.status-pending {
    background-color: #fff3e0;
    color: #e65100;
}

.status-approved {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-rejected {
    background-color: #ffebee;
    color: #c62828;
}

/* Company Info Styling */
.company-info .info-content {
    display: grid;
    gap: 10px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
}

.company-info p {
    margin: 0;
    color: #666;
}

.company-info strong {
    color: #333;
}

/* Progress Overview Styling */
.progress-stats {
    display: grid;
    gap: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.stat-item .label {
    color: #666;
}

.stat-item .value {
    font-weight: bold;
    color: #2c6929;
}

.progress-bar-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.progress-bar {
    flex-grow: 1;
    height: 10px;
    background: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #2c6929;
    transition: width 0.3s ease;
}

.percentage {
    min-width: 60px;
    text-align: right;
    color: #2c6929;
    font-weight: bold;
}

/* Recent Entries Styling */
.entries-list {
    display: grid;
    gap: 15px;
}

.entry-item {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 15px;
    border-left: 4px solid #2c6929;
}

.entry-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.entry-date {
    color: #666;
    font-size: 14px;
}

.entry-hours {
    font-weight: bold;
    color: #2c6929;
}

.entry-tasks {
    margin-bottom: 10px;
}

.entry-tasks p,
.entry-remarks p {
    margin: 5px 0;
    color: #666;
}

.entry-tasks strong,
.entry-remarks strong {
    color: #333;
    display: block;
    margin-bottom: 5px;
}

/* Status Info Styling */
.rejected-info,
.pending-info {
    padding: 15px;
    border-radius: 6px;
}

.rejected-info {
    background: #ffebee;
    color: #c62828;
}

.pending-info {
    background: #fff3e0;
    color: #e65100;
}

.remarks {
    margin-top: 15px;
}

.remarks h4 {
    color: #333;
    margin: 0 0 5px 0;
}

.remarks p {
    margin: 0;
    color: #666;
}

/* Button Styling */
.apply-btn,
.reapply-btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    margin-top: 10px;
    transition: background-color 0.2s;
}

.apply-btn {
    background: #2c6929;
    color: white;
}

.apply-btn:hover {
    background: #1e4c1c;
}

.reapply-btn {
    background: #c62828;
    color: white;
}

.reapply-btn:hover {
    background: #b71c1c;
}

.no-application {
    text-align: center;
    padding: 30px;
    background: #f5f5f5;
    border-radius: 6px;
}
</style>