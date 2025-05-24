<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';

const props = defineProps({
    auth: Object,
    application: Object,
    partner: Object,
    evaluations: Array
});

// Using computed properties for reactive prop values
const applicationStatus = computed(() => props.application?.status || null);
const partnerInfo = computed(() => props.partner || null);
const startDate = computed(() => props.application?.start_date || null);
const endDate = computed(() => props.application?.end_date || null);
const requiredHours = computed(() => props.application?.required_hours || 0);
const completedHours = computed(() => props.application?.completed_hours || 0);

// Safe getters for partner information
const getPartnerInfo = {
    name: computed(() => props.partner?.partnerName ?? 'N/A'),
    address: computed(() => props.partner?.partnerAddress ?? 'N/A'),
    phone: computed(() => props.partner?.partnerPhone ?? 'N/A'),
    email: computed(() => props.partner?.partnerEmail ?? 'N/A')
};

const getStatusClass = (status) => {
    const classes = {
        'Pending': 'status-pending',
        'Approved': 'status-approved',
        'Rejected': 'status-rejected'
    };
    return classes[status] || '';
};

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

const calculateProgress = () => {
    const required = requiredHours.value;
    const completed = completedHours.value;
    
    if (!required || required <= 0) return 0;
    return Math.min(100, Math.round((completed / required) * 100));
};
</script>

<template>
    <Head title="Student Dashboard" />

    <StudentLayout>
        <div class="dashboard-container">
            <div class="welcome-section">
                <h1>Welcome, {{ auth.user.name }}!</h1>
                <p class="student-id">Student ID: {{ auth.user.studentId }}</p>
            </div>

            <div class="dashboard-content">
                <div class="quick-actions-card">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <Link :href="route('student.application')" class="action-item">
                            <i class="fas fa-file-alt"></i>
                            <span>OJT Application</span>
                        </Link>
                        <Link :href="route('student.progress')" class="action-item">
                            <i class="fas fa-chart-line"></i>
                            <span>View Progress</span>
                        </Link>
                        <Link :href="route('student.info')" class="action-item">
                            <i class="fas fa-user-edit"></i>
                            <span>My Profile</span>
                        </Link>
                    </div>
                </div>

                <div class="status-card">
                    <h2>OJT Status Overview</h2>
                    <div v-if="applicationStatus" class="status-content">
                        <div class="status-header">
                            <span :class="['status-badge', getStatusClass(applicationStatus)]">
                                {{ applicationStatus }}
                            </span>
                            <Link 
                                v-if="applicationStatus === 'Rejected'" 
                                :href="route('student.application')"
                                class="reapply-btn"
                            >
                                Reapply
                            </Link>
                        </div>
                        
                        <div v-if="applicationStatus === 'Approved'" class="approved-details">
                            <div class="partner-info" v-if="partnerInfo">
                                <h3>Company Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="label">Company Name:</span>
                                        <span class="value">{{ getPartnerInfo.name }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Address:</span>
                                        <span class="value">{{ getPartnerInfo.address }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Contact:</span>
                                        <span class="value">{{ getPartnerInfo.phone }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Email:</span>
                                        <span class="value">{{ getPartnerInfo.email }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ojt-progress">
                                <h3>OJT Progress</h3>
                                <div class="progress-overview">
                                    <div class="time-details">
                                        <div class="detail-item">
                                            <span class="label">Start Date:</span>
                                            <span class="value">{{ formatDate(startDate) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">End Date:</span>
                                            <span class="value">{{ formatDate(endDate) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Required Hours:</span>
                                            <span class="value">{{ requiredHours }} hours</span>
                                        </div>
                                    </div>
                                    <div class="progress-section">
                                        <div class="progress-header">
                                            <span class="progress-label">Completion Progress</span>
                                            <span class="progress-percentage">{{ calculateProgress() }}%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div 
                                                class="progress-fill"
                                                :style="{ width: calculateProgress() + '%' }"
                                            ></div>
                                        </div>
                                        <div class="hours-counter">
                                            <span>{{ completedHours }} / {{ requiredHours }} hours completed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="applicationStatus === 'Pending'" class="pending-message">
                            <p>Your application is currently under review. We will notify you once there's an update.</p>
                            <p class="submission-note">You can track your application status here.</p>
                        </div>

                        <div v-if="applicationStatus === 'Rejected'" class="rejected-message">
                            <p>Unfortunately, your application was not approved. Please review the feedback and consider reapplying.</p>
                            <div class="feedback-section" v-if="props.application?.remarks">
                                <h4>Feedback:</h4>
                                <p>{{ props.application.remarks }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="no-application">
                        <p>You haven't submitted an OJT application yet.</p>
                        <Link :href="route('student.application')" class="apply-btn">
                            Apply Now
                        </Link>
                    </div>
                </div>

                <!-- Recent Evaluations -->
                <div v-if="evaluations?.length" class="evaluations-card">
                    <h2>Recent Evaluations</h2>
                    <div class="evaluations-list">
                        <div v-for="evaluation in evaluations" :key="evaluation.id" class="evaluation-item">
                            <div class="evaluation-header">
                                <span class="eval-date">{{ formatDate(evaluation.date) }}</span>
                                <span class="eval-score">Score: {{ evaluation.score }}/100</span>
                            </div>
                            <p class="eval-feedback">{{ evaluation.feedback }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

<style scoped>
.dashboard-container {
    padding: 20px;
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

.dashboard-content {
    display: grid;
    gap: 20px;
}

.status-card,
.evaluations-card,
.quick-actions-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-card h2,
.evaluations-card h2,
.quick-actions-card h2 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
}

.status-content {
    display: grid;
    gap: 20px;
}

.status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

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

.approved-details {
    display: grid;
    gap: 20px;
}

.partner-info {
    padding: 15px;
    background: #f5f5f5;
    border-radius: 6px;
}

.partner-info h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}

.info-grid {
    display: grid;
    gap: 10px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.info-item .label {
    color: #666;
}

.info-item .value {
    font-weight: 500;
}

.ojt-progress {
    margin-top: 20px;
}

.ojt-progress h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}

.progress-overview {
    display: grid;
    gap: 20px;
}

.time-details {
    display: grid;
    gap: 10px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.detail-item .label {
    color: #666;
}

.detail-item .value {
    font-weight: 500;
}

.progress-section {
    margin-top: 10px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.progress-label {
    font-weight: 500;
}

.progress-percentage {
    color: #666;
}

.progress-bar {
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #2c6929;
    transition: width 0.3s ease;
}

.hours-counter {
    margin-top: 5px;
    color: #666;
    font-size: 14px;
}

.pending-message {
    color: #e65100;
    padding: 15px;
    background: #fff3e0;
    border-radius: 6px;
}

.pending-message p {
    margin: 0 0 10px 0;
}

.pending-message .submission-note {
    font-size: 14px;
    color: #666;
}

.rejected-message {
    color: #c62828;
    padding: 15px;
    background: #ffebee;
    border-radius: 6px;
}

.rejected-message p {
    margin: 0 0 10px 0;
}

.rejected-message .feedback-section {
    margin-top: 15px;
}

.rejected-message .feedback-section h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.rejected-message .feedback-section p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.no-application {
    text-align: center;
    padding: 30px;
    background: #f5f5f5;
    border-radius: 6px;
}

.apply-btn,
.reapply-btn {
    display: inline-block;
    background: #2c6929;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    margin-top: 10px;
    transition: background-color 0.2s;
}

.apply-btn:hover,
.reapply-btn:hover {
    background: #1e4c1c;
}

.reapply-btn {
    background: #c62828;
}

.reapply-btn:hover {
    background: #b71c1c;
}

.actions-grid {
    display: grid;
    gap: 15px;
    grid-template-columns: repeat(3, 1fr);
}

.action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    border-radius: 8px;
    background: #f8fafc;
    text-decoration: none;
    color: #1e293b;
    transition: all 0.3s ease;
}

.action-item:hover {
    transform: translateY(-2px);
    background: #f1f5f9;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.action-item i {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    color: #3b82f6;
}

.action-item span {
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
}

@media (max-width: 768px) {
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .actions-grid {
        grid-template-columns: 1fr;
    }
}

.evaluations-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-top: 1.5rem;
}

.evaluations-list {
    margin-top: 1rem;
}

.evaluation-item {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.evaluation-item:last-child {
    border-bottom: none;
}

.evaluation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.eval-date {
    color: #64748b;
    font-size: 0.875rem;
}

.eval-score {
    font-weight: 500;
    color: #1e293b;
}

.eval-feedback {
    color: #475569;
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0;
}
</style>
