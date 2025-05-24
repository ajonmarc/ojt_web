<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head  } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';


const props = defineProps({
    auth: Object,
    existingApplication: Object,
});

const isEditing = ref(false);
const showDeleteConfirm = ref(false);

const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    return dateString.split('T')[0];
};

const form = useForm({
    resume: null,
    applicationLetter: null,
    preferredCompany: props.existingApplication?.preferred_company || '',
    startDate: formatDateForInput(props.existingApplication?.start_date) || '',
    endDate: formatDateForInput(props.existingApplication?.end_date) || '',
});

const resumePreview = ref(null);
const letterPreview = ref(null);
const resumeUrl = ref(null);
const letterUrl = ref(null);

const handleFileUpload = (event, type) => {
    const file = event.target.files[0];
    if (!file) return;

    // Get file extension
    const extension = file.name.split('.').pop().toLowerCase();
    
    if (type === 'resume') {
        form.resume = file;
        resumePreview.value = {
            name: file.name,
            size: formatFileSize(file.size),
            type: file.type,
            extension: extension
        };
        if (resumeUrl.value) {
            URL.revokeObjectURL(resumeUrl.value);
        }
        resumeUrl.value = URL.createObjectURL(file);
    } else {
        form.applicationLetter = file;
        letterPreview.value = {
            name: file.name,
            size: formatFileSize(file.size),
            type: file.type,
            extension: extension
        };
        if (letterUrl.value) {
            URL.revokeObjectURL(letterUrl.value);
        }
        letterUrl.value = URL.createObjectURL(file);
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIcon = (extension) => {
    const icons = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'ppt': 'fa-file-powerpoint',
        'pptx': 'fa-file-powerpoint',
        'txt': 'fa-file-alt',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image'
    };
    return icons[extension] || 'fa-file';
};

const submit = () => {
    if (isEditing.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PATCH',
        })).post(route('student.application.update', props.existingApplication.id), {
            onSuccess: () => {
                if (resumeUrl.value) URL.revokeObjectURL(resumeUrl.value);
                if (letterUrl.value) URL.revokeObjectURL(letterUrl.value);
                isEditing.value = false;
            },
        });
    } else {
        form.post(route('student.application.submit'), {
            onSuccess: () => {
                if (resumeUrl.value) URL.revokeObjectURL(resumeUrl.value);
                if (letterUrl.value) URL.revokeObjectURL(letterUrl.value);
            },
        });
    }
};

const deleteApplication = () => {
    if (confirm('Are you sure you want to delete this application?')) {
        router.delete(route('student.application.delete', props.existingApplication.id));
    }
};

const startEditing = () => {
    form.preferredCompany = props.existingApplication.preferred_company;
    form.startDate = formatDateForInput(props.existingApplication.start_date);
    form.endDate = formatDateForInput(props.existingApplication.end_date);
    isEditing.value = true;
};

const cancelEditing = () => {
    form.reset();
    form.clearErrors();
    isEditing.value = false;
};

const getStatusColor = (status) => {
    const colors = {
        'Pending': 'bg-yellow-100 text-yellow-800',
        'Approved': 'bg-green-100 text-green-800',
        'Rejected': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="OJT Application" />

    <StudentLayout>
        <div class="application-container">
            <div class="application-header">
                <h2>OJT Application</h2>
                <p v-if="!existingApplication && !isEditing" class="instructions">
                    Please fill out all required information and upload the necessary documents.
                </p>
            </div>

            <!-- Existing Application View -->
            <div v-if="existingApplication && !isEditing" class="existing-application">
                <div class="status-header">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span :class="['status-badge', getStatusColor(existingApplication.status)]">
                                {{ existingApplication.status }}
                            </span>
                            <span v-if="existingApplication.partner" class="ml-2 text-gray-600">
                                Assigned to: {{ existingApplication.partner.name }}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="startEditing" 
                                class="btn-secondary"
                                :disabled="existingApplication.status === 'Approved'"
                            >
                                Edit Application
                            </button>
                            <button 
                                @click="deleteApplication" 
                                class="btn-danger"
                                :disabled="existingApplication.status === 'Approved'"
                            >
                                Delete Application
                            </button>
                        </div>
                    </div>
                </div>

                <div class="application-details">
                    <div class="detail-group">
                        <h3>Documents</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="document-preview">
                                <h4>Resume</h4>
                                <a 
                                    :href="`/storage/${existingApplication.resume_path}`" 
                                    target="_blank"
                                    class="file-link"
                                >
                                    <i class="fas fa-file-alt"></i>
                                    View Resume
                                </a>
                            </div>
                            <div class="document-preview">
                                <h4>Application Letter</h4>
                                <a 
                                    :href="`/storage/${existingApplication.letter_path}`" 
                                    target="_blank"
                                    class="file-link"
                                >
                                    <i class="fas fa-file-alt"></i>
                                    View Application Letter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="detail-group">
                        <h3>Schedule Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label>Preferred Company</label>
                                <p>{{ existingApplication.preferred_company || 'Not specified' }}</p>
                            </div>
                            <div>
                                <label>Schedule</label>
                                <p>{{ existingApplication.start_date }} to {{ existingApplication.end_date }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="existingApplication.remarks" class="detail-group">
                        <h3>Remarks</h3>
                        <p class="remarks">{{ existingApplication.remarks }}</p>
                    </div>
                </div>
            </div>

            <!-- Application Form (New or Edit) -->
            <form v-else @submit.prevent="submit" class="application-form">
                <div class="form-section">
                    <h3>Documents</h3>
                    
                    <!-- Resume Upload -->
                    <div class="upload-group">
                        <label for="resume">Resume</label>
                        <div class="upload-container">
                            <input
                                type="file"
                                id="resume"
                                @change="(e) => handleFileUpload(e, 'resume')"
                                :required="!isEditing"
                            />
                            <div v-if="resumePreview" class="file-preview">
                                <i :class="`fas ${getFileIcon(resumePreview.extension)}`"></i>
                                <span>{{ resumePreview.name }} ({{ resumePreview.size }})</span>
                                <a v-if="resumeUrl" :href="resumeUrl" target="_blank" class="preview-link">Preview</a>
                            </div>
                        </div>
                        <p class="help-text">Upload your professional resume</p>
                    </div>

                    <!-- Application Letter Upload -->
                    <div class="upload-group">
                        <label for="applicationLetter">Application Letter</label>
                        <div class="upload-container">
                            <input
                                type="file"
                                id="applicationLetter"
                                @change="(e) => handleFileUpload(e, 'letter')"
                                :required="!isEditing"
                            />
                            <div v-if="letterPreview" class="file-preview">
                                <i :class="`fas ${getFileIcon(letterPreview.extension)}`"></i>
                                <span>{{ letterPreview.name }} ({{ letterPreview.size }})</span>
                                <a v-if="letterUrl" :href="letterUrl" target="_blank" class="preview-link">Preview</a>
                            </div>
                        </div>
                        <p class="help-text">Upload your application letter</p>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Preferred Schedule</h3>
                    
                    <!-- Preferred Company -->
                    <div class="form-group">
                        <label for="preferredCompany">Preferred Company (Optional)</label>
                        <input
                            type="text"
                            id="preferredCompany"
                            v-model="form.preferredCompany"
                            placeholder="Enter your preferred company name"
                        />
                        <p class="help-text">If you have a specific company in mind, enter it here</p>
                    </div>

                    <!-- Start Date -->
                    <div class="form-group">
                        <label for="startDate">Preferred Start Date</label>
                        <input
                            type="date"
                            id="startDate"
                            v-model="form.startDate"
                            required
                        />
                    </div>

                    <!-- End Date -->
                    <div class="form-group">
                        <label for="endDate">Preferred End Date</label>
                        <input
                            type="date"
                            id="endDate"
                            v-model="form.endDate"
                            required
                        />
                    </div>
                </div>

                <div class="form-actions">
                    <button 
                        v-if="isEditing" 
                        type="button" 
                        @click="cancelEditing"
                        class="btn-secondary mr-2"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="form.processing" 
                        class="btn-primary"
                    >
                        {{ form.processing ? 'Submitting...' : (isEditing ? 'Update Application' : 'Submit Application') }}
                    </button>
                </div>
            </form>
        </div>
    </StudentLayout>
</template>

<style scoped>
.application-container {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.application-header {
    margin-bottom: 30px;
}

.application-header h2 {
    color: #2c6929;
    margin: 0 0 10px 0;
}

.instructions {
    color: #666;
    font-size: 16px;
    margin: 0;
}

.application-form,
.existing-application {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h3,
.detail-group h3 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 18px;
}

.upload-group,
.form-group {
    margin-bottom: 20px;
}

.upload-group label,
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.upload-container {
    border: 2px dashed #ddd;
    padding: 20px;
    border-radius: 4px;
    background: #f9f9f9;
    transition: border-color 0.3s;
}

.upload-container:hover {
    border-color: #2c6929;
}

.upload-container input[type="file"] {
    width: 100%;
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 4px;
    font-size: 14px;
}

.file-preview i {
    font-size: 16px;
}

.file-preview i.fa-file-pdf {
    color: #dc3545;
}

.file-preview i.fa-file-word {
    color: #2b579a;
}

.file-preview i.fa-file-excel {
    color: #217346;
}

.file-preview i.fa-file-powerpoint {
    color: #d24726;
}

.file-preview i.fa-file-image {
    color: #4caf50;
}

.file-preview i.fa-file {
    color: #666;
}

.file-preview span {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.preview-link {
    color: #2c6929;
    text-decoration: none;
    padding: 4px 8px;
    background: #e8f5e9;
    border-radius: 4px;
    font-size: 12px;
    transition: all 0.2s;
}

.preview-link:hover {
    background: #c8e6c9;
    text-decoration: none;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus {
    border-color: #2c6929;
    outline: none;
}

.help-text {
    margin: 4px 0 0 0;
    font-size: 12px;
    color: #666;
}

.form-actions {
    margin-top: 30px;
    text-align: right;
}

.btn-primary {
    background: #2c6929;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-primary:hover {
    background: #1e4c1c;
}

.btn-primary:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.btn-secondary {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: #e9ecef;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-danger:disabled {
    background: #e4606d;
    cursor: not-allowed;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
}

.detail-group {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #eee;
}

.detail-group:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.detail-group h4 {
    color: #666;
    margin: 0 0 8px 0;
    font-size: 14px;
}

.document-preview {
    padding: 16px;
    background: #f8f9fa;
    border-radius: 4px;
}

.file-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #2c6929;
    text-decoration: none;
    font-size: 14px;
}

.file-link:hover {
    text-decoration: underline;
}

.remarks {
    padding: 12px;
    background: #fff3cd;
    border-radius: 4px;
    color: #856404;
    font-size: 14px;
}
</style>
