<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    program: '',
    email: '',
    studentId: '',
    yearLevel: '',
    contactNo: '',
    schoolEmail: '',
    personalEmail: '',
});

const submit = () => {
    form.put(route('student.info.update'), {
        onSuccess: () => {
            // Show success message
        },
    });
};
</script>

<template>
    <Head title="Student Info" />

    <StudentLayout>
        <div class="profile-container">
            <h2 class="dashboard-title">My Profile</h2>

            <!-- Profile Form -->
            <form @submit.prevent="submit" class="form-container">
                <div class="section-title">Personal Information</div>
                
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input
                        type="text"
                        id="fullName"
                        v-model="form.name"
                        placeholder="Enter your name"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="program">Program</label>
                    <input
                        type="text"
                        id="program"
                        v-model="form.program"
                        placeholder="Enter your program"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="studentId">Student ID</label>
                    <input
                        type="text"
                        id="studentId"
                        v-model="form.studentId"
                        placeholder="Enter your Student ID"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="yearLevel">Year Level</label>
                    <input
                        type="text"
                        id="yearLevel"
                        v-model="form.yearLevel"
                        placeholder="Enter your Year Level"
                        required
                    />
                </div>

                <div class="section-title">Contact Information</div>

                <div class="form-group">
                    <label for="contactNo">Contact Number</label>
                    <input
                        type="text"
                        id="contactNo"
                        v-model="form.contactNo"
                        placeholder="Enter your contact number"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="schoolEmail">School Email</label>
                    <input
                        type="email"
                        id="schoolEmail"
                        v-model="form.schoolEmail"
                        placeholder="Enter your school email"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="personalEmail">Personal Email</label>
                    <input
                        type="email"
                        id="personalEmail"
                        v-model="form.personalEmail"
                        placeholder="Enter your personal email"
                        required
                    />
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" :disabled="form.processing">Save Changes</button>
                    <button type="button" class="cancel" @click="form.reset()">Cancel</button>
                </div>
            </form>
        </div>
    </StudentLayout>
</template>

<style scoped>
.profile-container {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.section-title {
    font-size: 18px;
    font-weight: bold;
    color: #2c6929;
    margin: 20px 0 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid #2c6929;
}

.form-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
    color: #333;
    font-weight: 500;
}

input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

button {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
}

button[type="submit"] {
    background-color: #2c6929;
    color: white;
}

button[type="submit"]:hover {
    background-color: #1e4c1c;
}

button.cancel {
    background-color: #f0f0f0;
    color: #333;
}

button.cancel:hover {
    background-color: #e0e0e0;
}

button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 10px;
    }
    
    .form-buttons {
        flex-direction: column;
    }
    
    button {
        width: 100%;
    }
}
</style>
