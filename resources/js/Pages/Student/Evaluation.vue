<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Student Evaluation" />

    <StudentLayout>
        <h2 class="dashboard-title">My Evaluation</h2>

<!-- Add Evaluation Button -->
<button class="add-evaluation-btn">Add New Evaluation</button>

<!-- Evaluation Table -->
<table class="evaluation-table">
  <thead>
    <tr>
      <th>Evaluation Date</th>
      <th>Evaluator</th>
      <th>Score</th>
      <th>Comments</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>2024-10-15</td>
      <td>John Doe</td>
      <td>85</td>
      <td>Good performance overall.</td>
    </tr>
    <tr>
      <td>2024-10-22</td>
      <td>Jane Smith</td>
      <td>90</td>
      <td>Excellent understanding of the tasks.</td>
    </tr>
    <tr>
      <td>2024-10-29</td>
      <td>Mark Lee</td>
      <td>78</td>
      <td>Needs improvement in time management.</td>
    </tr>
    <!-- More evaluation entries can be added here -->
  </tbody>
</table>
    </StudentLayout>
</template>

<style scoped>
* {
  box-sizing: border-box;
}
body {
  display: flex;
  margin: 0;
  font-family: "Itim", cursive;
  overflow: hidden;
}

/* Sidebar styling */
.sidebar {
  width: 280px;
  background-color: #f7ce5a;
  padding: 20px;
  height: 100vh;
  display: flex;
  flex-direction: column;
  position: fixed;
  overflow-y: scroll; /* Enables scrolling */
  scrollbar-width: none; /* Firefox */
}
/* Hides scrollbar for Chrome, Safari, and Edge */
.sidebar::-webkit-scrollbar {
  display: none;
}
.sidebar a {
  display: flex;
  align-items: center;
  padding: 10px;
  color: black;
  text-decoration: none;
  font-size: 18px;
  margin-bottom: 15px;
  border-radius: 10px;
  transition: background-color 0.3s;
}
.sidebar a:hover {
  background-color: #ffe49c;
}
.sidebar a i {
  margin-right: 15px;
}

/* Active link styling */
.sidebar a.active {
  background-color: #ffe49c;
  font-weight: bold;
}

/* Header styling */
.header {
  height: 80px;
  background-color: #2c6929;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 30px;
  color: white;
  position: fixed;
  top: 0;
  width: calc(100% - 280px);
  margin-left: 280px;
  z-index: 10;
}

.admin-info {
  display: flex;
  align-items: center;
  background-color: white;
  padding: 10px 20px;
  border-radius: 30px;
  color: black;
}

/* Main content styling */
.main-content {
  width: calc(100% - 280px);
  margin-left: 280px;
  margin-top: 80px;
  padding: 20px;
  overflow-y: auto;
  height: calc(100vh - 80px);
}

.dashboard-title {
  font-size: 24px;
  margin-bottom: 20px;
}

/* Card grid styling */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

/* Card styling */
.card {
  background-color: #f0f6ed;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  padding: 20px;
  align-items: center;
  text-align: center;
}
.card h3 {
  font-size: 16px;
  margin: 10px 0;
}
.card .icon {
  font-size: 36px;
  margin-bottom: 10px;
}
.card .total-number {
  font-size: 24px;
  font-weight: bold;
  color: #2c6929;
  margin-bottom: 10px;
}
.view-btn {
  background-color: #2c6929;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
}
.view-btn:hover {
  background-color: #3d8040;
}

/* Logo and Title Styling */
.logo-section {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.logo {
  width: 40px; /* Adjust as needed */
  height: 40px; /* Adjust as needed */
  margin-right: 10px;
  border-radius: 50%; /* Optional: for rounded logo */
}

.sidebar-title {
  font-size: 14px;
  font-weight: bold;
  color: #000000;
}

/* Schedule Table styling */
.evaluation-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
.evaluation-table th,
.evaluation-table td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
}
.evaluation-table th {
  background-color: #f0f6ed;
}

/* Button styling */
.add-evaluation-btn {
  background-color: #2c6929;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  margin-bottom: 20px;
}
.add-evaluation-btn:hover {
  background-color: #3d8040;
}
</style>
