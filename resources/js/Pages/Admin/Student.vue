<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, watch, defineProps, computed } from "vue";
import InputError from "@/Components/InputError.vue";
import Swal from "sweetalert2";

const props = defineProps({
    users: Array,
    programs: Array,
});

// Reactive states
const isAddUser = ref(false);
const dialogVisible = ref(false);
const editUser = ref(null);
const searchQuery = ref("");
const filterStatus = ref("all");
const filterProgram = ref("all");
const sortBy = ref("name");
const sortOrder = ref("asc");

// Computed property to filter and sort students
const filteredUsers = computed(() => {
    let filtered = props.users.filter(user => {
        const query = searchQuery.value.toLowerCase();
        const matchesSearch = 
            user.role === "student" &&
            (user.name.toLowerCase().includes(query) ||
            user.studentId.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            user.studentPhone.toLowerCase().includes(query) ||
            user.status.toLowerCase().includes(query) ||
            user.ojtProgram.toLowerCase().includes(query));

        const matchesStatus = filterStatus.value === "all" || user.status === filterStatus.value;
        const matchesProgram = filterProgram.value === "all" || user.ojtProgram === filterProgram.value;

        return matchesSearch && matchesStatus && matchesProgram;
    });

    // Sort the filtered results
    return filtered.sort((a, b) => {
        const aValue = a[sortBy.value].toLowerCase();
        const bValue = b[sortBy.value].toLowerCase();
        return sortOrder.value === "asc" 
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
    });
});

// Statistics
const totalStudents = computed(() => filteredUsers.value.length);
const activeStudents = computed(() => 
    filteredUsers.value.filter(user => user.status === "Active").length
);
const inactiveStudents = computed(() => 
    filteredUsers.value.filter(user => user.status === "Inactive").length
);

// Form handling
const form = useForm({
    name: "",
    email: "",
    password: "",
    studentId: "",
    studentPhone: "",
    ojtProgram: "",
    status: "",
});

// Modal functions
const openAddUser = () => {
    isAddUser.value = true;
    dialogVisible.value = true;
    form.reset();
    form.password = ""; // Ensure password is empty for new user
};

const openEditUser = (user) => {
    isAddUser.value = false;
    dialogVisible.value = true;
    editUser.value = user;
    
    // Reset form to avoid carrying over old values
    form.reset();
    
    // Only set the fields we want to edit
    form.name = user.name;
    form.email = user.email;
    form.studentPhone = user.studentPhone;
    form.studentId = user.studentId;
    form.ojtProgram = user.ojtProgram;
    form.status = user.status;
    form.password = ""; // Clear password field for edit mode
};

const closeModal = () => {
    dialogVisible.value = false;
    form.reset();
    editUser.value = null;
};

// Form submission
const submit = () => {
    if (isAddUser.value) {
        // For new users, we need all fields including password
        router.post(route("user.store"), form, {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: "Success!",
                    text: "Student created successfully.",
                    icon: "success",
                    toast: true,
                    position: "bottom-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            },
        });
    } else {
        // For editing, prepare update data
        const updateData = {
            _method: 'PATCH',
            name: form.name,
            email: form.email,
            studentPhone: form.studentPhone,
            studentId: form.studentId,
            ojtProgram: form.ojtProgram,
            status: form.status,
        };

        // Only include password if it's a non-empty string
        if (form.password?.trim()) {
            updateData.password = form.password;
        }
        
        router.post(route("user.update", editUser.value.id), updateData, {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: "Success!",
                    text: "Student updated successfully.",
                    icon: "success",
                    toast: true,
                    position: "bottom-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            },
        });
    }
};

// Delete user
const deleteUser = (user) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("user.destroy", user.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Student has been deleted.",
                        icon: "success",
                        toast: true,
                        position: "bottom-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Admin Student" />

    <AdminLayout>
        <div class="student-container">
            <!-- Header Section -->
            <div class="header-section">
                <h2 class="dashboard-title">Students Management</h2>
                <button class="add-btn" @click="openAddUser">
                    <i class="fas fa-plus"></i> Add New Student
                </button>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        placeholder="Search students..."
                        v-model="searchQuery"
                    />
                </div>

                <div class="filter-controls">
                    <select v-model="filterStatus">
                        <option value="all">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>

                    <select v-model="filterProgram">
                        <option value="all">All Programs</option>
                        <option v-for="program in programs" :key="program.id" :value="program.programName">
                            {{ program.programName }}
                        </option>
                    </select>

                    <select v-model="sortBy">
                        <option value="name">Sort by Name</option>
                        <option value="studentId">Sort by ID</option>
                        <option value="ojtProgram">Sort by Program</option>
                        <option value="status">Sort by Status</option>
                    </select>

                    <button @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'" class="sort-btn">
                        <i :class="['fas', sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down']"></i>
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Total Students</h3>
                        <p>{{ totalStudents }}</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3>Active</h3>
                        <p>{{ activeStudents }}</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏸️</div>
                    <div class="stat-info">
                        <h3>Inactive</h3>
                        <p>{{ inactiveStudents }}</p>
                    </div>
                </div>
            </div>

            <!-- Student Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Program</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id">
                            <td>{{ user.studentId }}</td>
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.studentPhone }}</td>
                            <td>{{ user.ojtProgram }}</td>
                            <td>
                                <span :class="['status-badge', user.status.toLowerCase()]">
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="actions">
                                <button @click="openEditUser(user)" class="edit-btn">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="deleteUser(user)" class="delete-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Student Modal -->
        <el-dialog
            v-model="dialogVisible"
            :title="isAddUser ? 'Add New Student' : 'Edit Student'"
            width="500px"
            @close="closeModal"
        >
            <form @submit.prevent="submit" class="student-form">
                <div class="form-group">
                    <label for="studentId">Student ID</label>
                    <input
                        type="text"
                        id="studentId"
                        v-model="form.studentId"
                        required
                    />
                    <InputError :message="form.errors.studentId" />
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        v-model="form.name"
                        required
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        v-model="form.email"
                        required
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="form-group" v-if="isAddUser">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        v-model="form.password"
                        required
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="form-group" v-if="!isAddUser">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input
                        type="password"
                        id="password"
                        v-model="form.password"
                        placeholder="Enter new password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="form-group">
                    <label for="studentPhone">Phone Number</label>
                    <input
                        type="tel"
                        id="studentPhone"
                        v-model="form.studentPhone"
                        required
                    />
                    <InputError :message="form.errors.studentPhone" />
                </div>

                <div class="form-group">
                    <label for="ojtProgram">OJT Program</label>
                    <select
                        id="ojtProgram"
                        v-model="form.ojtProgram"
                        required
                    >
                        <option value="">Select Program</option>
                        <option
                            v-for="program in programs"
                            :key="program.id"
                            :value="program.programName"
                        >
                            {{ program.programName }}
                        </option>
                    </select>
                    <InputError :message="form.errors.ojtProgram" />
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select
                        id="status"
                        v-model="form.status"
                        required
                    >
                        <option value="">Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="form-actions">
                    <button
                        type="button"
                        @click="closeModal"
                        class="cancel-btn"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="submit-btn"
                    >
                        {{ isAddUser ? 'Add Student' : 'Update Student' }}
                    </button>
                </div>
            </form>
        </el-dialog>
    </AdminLayout>
</template>

<style scoped>
.student-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.dashboard-title {
    font-size: 24px;
    color: #333;
    margin: 0;
}

.add-btn {
    background-color: #f7ce5a;
    color: #333;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.2s;
}

.add-btn:hover {
    background-color: #f5c431;
}

.filters-section {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 300px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.search-box input {
    width: 100%;
    padding: 10px 10px 10px 35px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.filter-controls {
    display: flex;
    gap: 10px;
}

.filter-controls select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: white;
}

.sort-btn {
    background: none;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 0 12px;
    cursor: pointer;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    font-size: 24px;
}

.stat-info h3 {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.stat-info p {
    margin: 5px 0 0;
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.table-container {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background-color: #f8f9fa;
    color: #666;
    font-weight: 600;
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

.status-badge.inactive {
    background-color: #fff1f0;
    color: #cf1322;
}

.actions {
    display: flex;
    gap: 8px;
}

.edit-btn, .delete-btn {
    border: none;
    padding: 6px;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.2s;
}

.edit-btn {
    background-color: #f7ce5a;
    color: #333;
}

.delete-btn {
    background-color: #ff4d4f;
    color: white;
}

.edit-btn:hover, .delete-btn:hover {
    opacity: 0.8;
}

/* Form Styling */
.student-form {
    display: grid;
    gap: 15px;
}

.form-group {
    display: grid;
    gap: 5px;
}

.form-group label {
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.cancel-btn,
.submit-btn {
    padding: 8px 16px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.cancel-btn {
    background-color: #f5f5f5;
    color: #666;
}

.submit-btn {
    background-color: #f7ce5a;
    color: #333;
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .filters-section {
        flex-direction: column;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-controls {
        flex-wrap: wrap;
    }

    .filter-controls select {
        flex: 1;
        min-width: 150px;
    }
}
</style>
