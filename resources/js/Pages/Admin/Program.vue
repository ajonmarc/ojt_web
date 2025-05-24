<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, watch, defineProps, computed } from "vue";
import InputError from "@/Components/InputError.vue";
import Swal from "sweetalert2";

const props = defineProps({
    programs: Array,
});

const isAddProgram = ref(false);
const dialogVisible = ref(false);
const editProgram = ref(null);

const form = useForm({
  programName: "",
  programDescription: "",
});

const openAddProgram = () => {
    isAddProgram.value = true;
    dialogVisible.value = true;
    form.reset();
};

const openEditProgram = (program) => {
    isAddProgram.value = false;
    dialogVisible.value = true;
    editProgram.value = program;

    // Pre-fill form
    form.programName = program.programName;
    form.programDescription = program.programDescription;
};

const handleClose = (done) => {
    closeModal();
    if (done) done();
};

const submit = () => {
    if (isAddProgram.value) {
        form.post(route("program.store"), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: "Success!",
                    text: "Program created successfully.",
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
        form.patch(route("program.update", editProgram.value.id), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: "Success!",
                    text: "Program updated successfully.",
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

const closeModal = () => {
    dialogVisible.value = false;
    form.reset();
    editProgram.value = null;
};

const deleteProgram = (program) => {
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
            form.delete(route("program.destroy", program.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Program deleted.",
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
    });
};
</script>

<template>
    <Head title="Admin Program" />

    <AdminLayout>
        <h2 class="dashboard-title">OJT programs</h2>
        <button type="button" class="add-student-btn" @click="openAddProgram">
            Add New Program
        </button>

        <!-- OJT Programs Table -->
        <table id="ojtProgramsTable" class="programs-table">
            <thead>
                <tr>
                    <th>Program ID</th>
                    <th>Program Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="program in programs" :key="program.id">
                    <td>{{ program.id }}</td>
                    <td>{{ program.programName }}</td>
                    <td>{{ program.programDescription }}</td>
                    <td>
                        <button type="button" class="edit-btn" @click="openEditProgram(program)">Edit</button>
                        <button type="button" class="delete-btn" @click="deleteProgram(program)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>

    <el-dialog
        v-model="dialogVisible"
        :title="isAddProgram ? 'Add Program' : 'Edit Program'"
        width="500"
        :before-close="handleClose"
    >
        <form @submit.prevent="submit" class="program-form">
            <div class="form-group">
                <label for="programName">Program Name:</label>
                <input type="text" id="programName" v-model="form.programName" />
                <InputError :message="form.errors.programName" />
            </div>

            <div class="form-group">
                <label for="programDescription">Description:</label>
                <textarea
                    id="programDescription"
                    v-model="form.programDescription"
                ></textarea>
                <InputError :message="form.errors.programDescription" />
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Save</button>
                <button type="button" class="cancel-btn" @click="closeModal">Cancel</button>
            </div>
        </form>
    </el-dialog>
</template>

<style scoped>
.programs-table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.programs-table th {
    padding: 10px;
    background-color: #f0f6ed;
}

.programs-table td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: left;
}

.program-form {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group textarea {
    height: 100px;
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.submit-btn,
.cancel-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.submit-btn {
    background-color: #4CAF50;
    color: white;
}

.cancel-btn {
    background-color: #f44336;
    color: white;
}

.edit-btn,
.delete-btn {
    padding: 6px 12px;
    margin: 0 4px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.edit-btn {
    background-color: #2196F3;
    color: white;
}

.delete-btn {
    background-color: #f44336;
    color: white;
}
</style>