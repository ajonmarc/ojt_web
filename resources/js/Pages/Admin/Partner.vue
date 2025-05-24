<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted, watch, defineProps, computed } from "vue";
import InputError from "@/Components/InputError.vue";
import Swal from "sweetalert2";

const props = defineProps({
    partners: Array,
});

const isAddPartner = ref(false);
const dialogVisible = ref(false);
const editPartner = ref(null);

const form = useForm({
    partnerName: "",
    partnerAddress: "",
    partnerPhone: "",
    partnerEmail: "",
    partnerContact: "",
    status: "Active",
});

const openAddPartner = () => {
    isAddPartner.value = true;
    dialogVisible.value = true;
    form.reset();
    form.status = "Active"; // Set default status
};

const openEditPartner = (partner) => {
    isAddPartner.value = false;
    dialogVisible.value = true;
    editPartner.value = partner;
    
    form.partnerName = partner.partnerName;
    form.partnerAddress = partner.partnerAddress;
    form.partnerPhone = partner.partnerPhone;
    form.partnerEmail = partner.partnerEmail;
    form.partnerContact = partner.partnerContact;
    form.status = partner.status;
};

const handleClose = (done) => {
    closeModal();
    if (done) done();
};

const submit = () => {
    if (isAddPartner.value) {
        form.post(route("partner.store"), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: "Success!",
                    text: "Partner added successfully.",
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
        form.patch(route("partner.update", editPartner.value.id), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: "Success!",
                    text: "Partner updated successfully.",
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
    editPartner.value = null;
};

const deletePartner = (partner) => {
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
            form.delete(route("partner.destroy", partner.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Partner has been deleted.",
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
    <Head title="Admin Partner" />

    <AdminLayout>
        <h2 class="dashboard-title">OJT Partners</h2>
        <button type="button" class="add-partner-btn" @click="openAddPartner">
            Add New Partner
        </button>

        <!-- Partners Table -->
        <table class="partners-table">
            <thead>
                <tr>
                    <th>Partner Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Contact Person</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="partner in partners" :key="partner.id">
                    <td>{{ partner.partnerName }}</td>
                    <td>{{ partner.partnerAddress }}</td>
                    <td>{{ partner.partnerPhone }}</td>
                    <td>{{ partner.partnerEmail }}</td>
                    <td>{{ partner.partnerContact }}</td>
                    <td>
                        <span :class="['status-badge', partner.status.toLowerCase()]">
                            {{ partner.status }}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="edit-btn" @click="openEditPartner(partner)">
                            Edit
                        </button>
                        <button type="button" class="delete-btn" @click="deletePartner(partner)">
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>

    <el-dialog
        v-model="dialogVisible"
        :title="isAddPartner ? 'Add Partner' : 'Edit Partner'"
        width="500"
        :before-close="handleClose"
    >
        <form @submit.prevent="submit" class="partner-form">
            <div class="form-group">
                <label for="partnerName">Partner Name:</label>
                <input 
                    type="text" 
                    id="partnerName" 
                    v-model="form.partnerName" 
                    required
                />
                <InputError :message="form.errors.partnerName" />
            </div>

            <div class="form-group">
                <label for="partnerAddress">Address:</label>
                <input 
                    type="text" 
                    id="partnerAddress" 
                    v-model="form.partnerAddress" 
                    required
                />
                <InputError :message="form.errors.partnerAddress" />
            </div>

            <div class="form-group">
                <label for="partnerPhone">Phone:</label>
                <input 
                    type="text" 
                    id="partnerPhone" 
                    v-model="form.partnerPhone" 
                    required
                />
                <InputError :message="form.errors.partnerPhone" />
            </div>

            <div class="form-group">
                <label for="partnerEmail">Email:</label>
                <input 
                    type="email" 
                    id="partnerEmail" 
                    v-model="form.partnerEmail" 
                    required
                />
                <InputError :message="form.errors.partnerEmail" />
            </div>

            <div class="form-group">
                <label for="partnerContact">Contact Person:</label>
                <input 
                    type="text" 
                    id="partnerContact" 
                    v-model="form.partnerContact" 
                    required
                />
                <InputError :message="form.errors.partnerContact" />
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" v-model="form.status" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <InputError :message="form.errors.status" />
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Save</button>
                <button type="button" class="cancel-btn" @click="closeModal">Cancel</button>
            </div>
        </form>
    </el-dialog>
</template>

<style scoped>
.partners-table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.partners-table th,
.partners-table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

.partners-table th {
    background-color: #f0f6ed;
    font-weight: bold;
}

.partners-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.partners-table tr:hover {
    background-color: #f5f5f5;
}

.add-partner-btn {
    background-color: #2c6929;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.add-partner-btn:hover {
    background-color: #3d8040;
}

.edit-btn,
.delete-btn {
    padding: 6px 12px;
    margin: 0 4px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.edit-btn {
    background-color: #2196F3;
    color: white;
}

.delete-btn {
    background-color: #f44336;
    color: white;
}

.partner-form {
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
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
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
    font-size: 14px;
}

.submit-btn {
    background-color: #4CAF50;
    color: white;
}

.cancel-btn {
    background-color: #f44336;
    color: white;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.active {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-badge.inactive {
    background-color: #ffebee;
    color: #c62828;
}
</style>