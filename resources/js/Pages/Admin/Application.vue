<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted, watch } from "vue";
import Swal from "sweetalert2";
import AddApplicationModal from "@/Components/AddApplicationModal.vue";

const props = defineProps({
    applications: Array,
    partners: Array,
    students: Array
});

const dialogVisible = ref(false);
const selectedApplication = ref(null);
const showAddModal = ref(false);

const form = useForm({
    status: "",
    partner_id: "",
    remarks: "",
    startDate: "",
    endDate: "",
    requiredHours: 600,
});

const openReviewDialog = (application) => {
    selectedApplication.value = application;
    dialogVisible.value = true;
    
    form.status = application.status;
    form.partner_id = application.partner?.id || "";
    form.remarks = application.remarks || "";
    form.startDate = application.startDate || "";
    form.endDate = application.endDate || "";
    form.requiredHours = application.requiredHours;
};

const handleClose = (done) => {
    closeModal();
    if (done) done();
};

const submitReview = () => {
    form.patch(route("application.update", selectedApplication.value.id), {
        onSuccess: () => {
            closeModal();
            showSuccessMessage("Application status updated successfully!");
        },
    });
};

const closeModal = () => {
    dialogVisible.value = false;
    form.reset();
    selectedApplication.value = null;
};

const deleteApplication = (application) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route("application.destroy", application.id), {
                onSuccess: () => {
                    showSuccessMessage("Application deleted successfully!");
                },
            });
        }
    });
};

const showSuccessMessage = (message) => {
    Swal.fire({
        title: "Success!",
        text: message,
        icon: "success",
        toast: true,
        position: "bottom-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
};

const handleApplicationAdded = () => {
    showAddModal.value = false;
    showSuccessMessage("Application added successfully!");
};

const getStatusClass = (status) => {
    const classes = {
        'Pending': 'status-pending',
        'Approved': 'status-approved',
        'Rejected': 'status-rejected'
    };
    return classes[status] || '';
};
</script>

<template>
    <Head title="Admin Application" />

    <AdminLayout>
        <div class="application-container">
            <div class="header">
                <h2 class="dashboard-title">Student OJT Applications</h2>
                <button class="add-btn" @click="showAddModal = true">
                    <i class="fas fa-plus"></i> Add Application
                </button>
            </div>

            <!-- Applications Table -->
            <div class="table-container">
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Program</th>
                            <th>Application Date</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="application in applications" :key="application.id">
                            <td>{{ application.user.name }}</td>
                            <td>{{ application.user.studentId }}</td>
                            <td>{{ application.user.program }}</td>
                            <td>{{ application.applicationDate }}</td>
                            <td>
                                <div class="document-links">
                                    <a v-if="application.hasResume" 
                                    :href="route('application.download.resume', application.id)"
                                    target="_blank"
                                    class="document-link">
                                        <i class="fas fa-file-pdf"></i> Resume
                                    </a>
                                    <a v-if="application.hasLetter" 
                                    :href="route('application.download.letter', application.id)"
                                    target="_blank"
                                    class="document-link">
                                        <i class="fas fa-file-alt"></i> Letter
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span :class="['status-badge', getStatusClass(application.status)]">
                                    {{ application.status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="review-btn" @click="openReviewDialog(application)">
                                        <i class="fas fa-check-circle"></i> Review
                                    </button>
                                    <button type="button" class="delete-btn" @click="deleteApplication(application)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="applications.length === 0">
                            <td colspan="7" class="no-data">No applications found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>

    <!-- Add Application Modal -->
    <AddApplicationModal
        v-model="showAddModal"
        :students="students"
        :partners="partners"
        @application-added="handleApplicationAdded"
    />

    <!-- Review Application Modal -->
    <el-dialog
        v-model="dialogVisible"
        title="Review Application"
        width="500"
        :before-close="handleClose"
    >
        <div v-if="selectedApplication" class="review-form">
            <div class="student-info">
                <h3>{{ selectedApplication.user.name }}</h3>
                <p class="student-details">
                    <span>ID: {{ selectedApplication.user.studentId }}</span>
                    <span>Program: {{ selectedApplication.user.program }}</span>
                </p>
            </div>

            <form @submit.prevent="submitReview" class="application-form">
                <div class="form-group">
                    <label for="status">Application Status:</label>
                    <select id="status" v-model="form.status" required>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approve</option>
                        <option value="Rejected">Reject</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="partner_id">Assign Partner Company:</label>
                    <select id="partner_id" v-model="form.partner_id">
                        <option value="">Select Partner</option>
                        <option v-for="partner in partners" :key="partner.id" :value="partner.id">
                            {{ partner.partnerName }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="startDate">Start Date:</label>
                    <input type="date" id="startDate" v-model="form.startDate" />
                </div>

                <div class="form-group">
                    <label for="endDate">End Date:</label>
                    <input type="date" id="endDate" v-model="form.endDate" />
                </div>

                <div class="form-group">
                    <label for="requiredHours">Required Hours:</label>
                    <input type="number" id="requiredHours" v-model="form.requiredHours" required min="1" />
                </div>

                <div class="form-group">
                    <label for="remarks">Remarks/Feedback:</label>
                    <textarea id="remarks" v-model="form.remarks" rows="3" placeholder="Add any comments, feedback, or requirements"></textarea>
                </div>

                <div class="form-buttons">
                    <button type="submit" :disabled="form.processing" class="submit-btn">
                        Submit Review
                    </button>
                    <button type="button" class="cancel-btn" @click="closeModal">Cancel</button>
                </div>
            </form>
        </div>
    </el-dialog>
</template>

<style scoped>
.application-container {
    padding: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.dashboard-title {
    font-size: 24px;
    color: #2c6929;
}

.add-btn {
    background-color: #2c6929;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background-color 0.2s;
}

.add-btn:hover {
    background-color: #1e4c1c;
}

.add-btn i {
    font-size: 0.875rem;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.applications-table {
    width: 100%;
    border-collapse: collapse;
}

.applications-table th,
.applications-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.applications-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
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

.document-links {
    display: flex;
    gap: 8px;
}

.document-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #2c6929;
    background-color: #e8f5e9;
    text-decoration: none;
}

.document-link:hover {
    background-color: #c8e6c9;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.review-btn,
.delete-btn {
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    color: white;
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
}

.review-btn {
    background-color: #2c6929;
}

.delete-btn {
    background-color: #d32f2f;
}

.review-btn:hover {
    background-color: #1e4c1c;
}

.delete-btn:hover {
    background-color: #c62828;
}

.no-data {
    text-align: center;
    color: #666;
    padding: 20px;
}

/* Review Form Styling */
.review-form {
    padding: 20px;
}

.student-info {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.student-info h3 {
    margin: 0 0 8px 0;
    color: #2c6929;
}

.student-details {
    display: flex;
    gap: 20px;
    color: #666;
    font-size: 14px;
}

.application-form {
    display: grid;
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-group label {
    font-weight: 500;
    color: #333;
}

.form-group select,
.form-group input,
.form-group textarea {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.submit-btn,
.cancel-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.submit-btn {
    background-color: #2c6929;
    color: white;
}

.cancel-btn {
    background-color: #f5f5f5;
    color: #333;
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
