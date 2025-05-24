<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-section">
                <img src="/assets/logo.jpg" alt="Logo" class="logo" />
                <h2 class="sidebar-title">OJT Management System</h2>
            </div>
            <nav>
                <!-- Add active class based on Inertia's route().current() -->
                <Link
                    :href="route('admin.dashboard')"
                    :class="{ active: route().current('admin.dashboard') }"
                >
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </Link>
                <Link  :href="route('admin.student')"
                :class="{ active: route().current('admin.student') }"> <i class="fas fa-users"></i>Students </Link>
                <Link  :href="route('admin.program')"
                :class="{ active: route().current('admin.program') }"> <i class="fas fa-book"></i>OJT Programs </Link>
                <Link  :href="route('admin.partner')"
                :class="{ active: route().current('admin.partner') }"> <i class="fas fa-handshake"></i>Partners </Link>
                <Link  :href="route('admin.application')"
                :class="{ active: route().current('admin.application') }"> <i class="fas fa-envelope"></i>OJT Applications </Link>
                <Link  :href="route('admin.report')"
                :class="{ active: route().current('admin.report') }"> <i class="fas fa-chart-bar"></i>Reports </Link>
                <Link :href="route('logout')" method="post">
                    <i class="fas fa-sign-out-alt"></i>Log Out
                </Link>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1">
            <!-- Header -->
            <div class="header">
                <Link :href="route('admin.profile')">
                    <div class="admin-info">
                        <span> {{ $page.props.auth.user.name }} </span>
                    </div>
                </Link>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <slot />
            </div>
        </div>
    </div>
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

/* Active link styling */
.sidebar a.active {
    background-color: #ffe49c;
    font-weight: bold;
}
</style>
