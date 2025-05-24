<template>
    <el-dialog
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        title="Add New Application"
        width="500"
        :before-close="handleClose"
    >
        <div class="add-application-form">
            <form @submit.prevent="submitForm" class="application-form">
                <div class="form-group">
                    <label for="student">Select Student:</label>
                    <select id="student" v-model="form.user_id" required>
                        <option value="">Select Student</option>
                        <option v-for="student in students" :key="student.id" :value="student.id">
                            {{ student.name }} ({{ student.studentId }})
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="partner_id">Assign Partner Company:</label>
                    <select id="partner_id" v-model="form.partner_id" required>
                        <option value="">Select Partner</option>
                        <option v-for="partner in partners" :key="partner.id" :value="partner.id">
                            {{ partner.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="startDate">Start Date:</label>
                    <input 
                        type="date" 
                        id="startDate" 
                        v-model="form.startDate"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="endDate">End Date:</label>
                    <input 
                        type="date" 
                        id="endDate" 
                        v-model="form.endDate"
                        required
                    >
                    <span v-if="formErrors.dates" class="error-message">{{ formErrors.dates }}</span>
                </div>

                <div class="form-group">
                    <label for="requiredHours">Required Hours:</label>
                    <input 
                        type="number" 
                        id="requiredHours" 
                        v-model="form.requiredHours"
                        min="1"
                        max="2000"
                        required
                    >
                    <span v-if="formErrors.requiredHours" class="error-message">{{ formErrors.requiredHours }}</span>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" v-model="form.status" required>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="remarks">Remarks:</label>
                    <textarea 
                        id="remarks" 
                        v-model="form.remarks"
                        rows="3"
                    ></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="cancel-btn" @click="$emit('update:modelValue', false)">
                        Cancel
                    </button>
                    <button type="submit" class="submit-btn" :disabled="form.processing || !isFormValid">
                        Add Application
                    </button>
                </div>
            </form>
        </div>
    </el-dialog>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    students: Array,
    partners: Array
});

const emit = defineEmits(['update:modelValue', 'application-added']);

const form = useForm({
    user_id: '',
    partner_id: '',
    startDate: '',
    endDate: '',
    requiredHours: 500,
    status: 'Pending',
    remarks: ''
});

const formErrors = computed(() => {
    const errors = {};
    
    if (form.startDate && form.endDate) {
        const start = new Date(form.startDate);
        const end = new Date(form.endDate);
        if (start >= end) {
            errors.dates = 'End date must be after start date';
        }
    }
    
    if (form.requiredHours <= 0) {
        errors.requiredHours = 'Required hours must be greater than 0';
    }
    
    if (form.requiredHours > 2000) {
        errors.requiredHours = 'Required hours cannot exceed 2000';
    }
    
    return errors;
});

const isFormValid = computed(() => {
    return Object.keys(formErrors.value).length === 0;
});

const handleClose = (done) => {
    form.reset();
    form.clearErrors();
    emit('update:modelValue', false);
    if (done) done();
};

const submitForm = () => {
    if (!isFormValid.value) {
        return;
    }

    form.post(route('application.store'), {
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            emit('update:modelValue', false);
            emit('application-added');
        },
        onError: (errors) => {
            console.error('Form submission failed:', errors);
        }
    });
};
</script>

<style scoped>
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-group select,
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: white;
}

.form-group textarea {
    resize: vertical;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}

.cancel-btn,
.submit-btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.cancel-btn {
    background-color: #e5e7eb;
    color: #374151;
    border: none;
}

.cancel-btn:hover {
    background-color: #d1d5db;
}

.submit-btn {
    background-color: #2c6929;
    color: white;
    border: none;
}

.submit-btn:hover {
    background-color: #1e4c1c;
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.error-message {
    color: #dc2626;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}
</style>
