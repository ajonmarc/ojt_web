<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <body>
        <header class="header">
            <div class="logo-title">
                <img src="/assets/logo.jpg" alt="Logo" class="logo" />
                <span class="title">OJT Management System</span>
            </div>

            <div class="search-bar">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        class="form-control search-input"
                        placeholder="Search..."
                    />
                </div>

                <i class="fas fa-bell notification-icon ms-3"></i>
            </div>

            <div class="nav-buttons" v-if="canLogin">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')">
                    Dashboard
                </Link>

                <template v-else>
                    <Link :href="route('login')"> Log in </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        style="display: none"
                    >
                        Register
                    </Link>
                </template>
            </div>
        </header>

        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">
                    Manage Your On-the-Job Training Program...
                </h1>
                <p class="hero-subtitle">
                    Track trainee progress, streamline communication, and
                    evaluate performance—all in one platform.
                </p>
            </div>
        
                <Link :href="route('login')" class="get-started-btn">
                    Get Started
                </Link>
        
        </section>

        <section
            class="features-section d-flex flex-column align-items-center"
            style="padding: 50px 300px"
        >
            <h2 class="text-center" style="margin-bottom: 50px">
                Key Features
            </h2>
            <div class="row justify-content-center">
                <div class="col-md-6 d-flex justify-content-center feature">
                    <div class="card rounded-circle border-0 text-center">
                        <i class="fas fa-tasks fa-3x my-3"></i>
                        <h3 class="feature-title">Centralized Tracking</h3>
                        <p>
                            Keep track of trainee progress and activities in one
                            place.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-center feature">
                    <div class="card rounded-circle border-0 text-center">
                        <i class="fas fa-calendar-alt fa-3x my-3"></i>
                        <h3 class="feature-title">Flexible Scheduling</h3>
                        <p>Plan and adjust training schedules easily.</p>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-center feature">
                    <div class="card rounded-circle border-0 text-center">
                        <i class="fas fa-check-circle fa-3x my-3"></i>
                        <h3 class="feature-title">Standardized Evaluation</h3>
                        <p>
                            Implement consistent evaluation metrics for all
                            trainees.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-center feature">
                    <div class="card rounded-circle border-0 text-center">
                        <i class="fas fa-lock fa-3x my-3"></i>
                        <h3 class="feature-title">Secure Documentation</h3>
                        <p>
                            Store all training documents securely and
                            accessibly.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="how-it-works-section d-flex flex-column align-items-center"
            style="padding: 50px 100px"
        >
            <h2 class="text-center" style="margin-bottom: 50px">
                How It Works
            </h2>
            <div class="row justify-content-center">
                <div class="col-md-6 d-flex justify-content-center mb-4">
                    <div
                        class="card text-center how-it-works-card"
                        style="height: 380px; width: 416px"
                    >
                        <h3 class="font-weight-bold">
                            Sign Up & Create Account
                        </h3>
                        <h3
                            class="how-it-works-title font-weight-bold"
                            style="font-size: 3rem"
                        >
                            1
                        </h3>
                        <p>
                            Easily sign up and create your account to get
                            started.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 d-flex justify-content-center mb-4">
                    <div
                        class="card text-center how-it-works-card"
                        style="height: 345px; width: 378px"
                    >
                        <h3 class="font-weight-bold">
                            Assign Tasks & Monitor Progress
                        </h3>
                        <h3
                            class="how-it-works-title font-weight-bold"
                            style="font-size: 2.5rem"
                        >
                            2
                        </h3>
                        <p>
                            Assign tasks to trainees and monitor their progress
                            effectively.
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 d-flex justify-content-center">
                    <div
                        class="card text-center how-it-works-card"
                        style="height: 275.87px; width: 302px"
                    >
                        <h3 class="font-weight-bold">
                            Evaluate & Provide Feedback
                        </h3>
                        <h3
                            class="how-it-works-title font-weight-bold"
                            style="font-size: 2.5rem"
                        >
                            3
                        </h3>
                        <p>
                            Assess trainee performance and provide constructive
                            feedback.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="ready-to-get-started d-flex flex-column justify-content-center align-items-center"
            style="height: 875px"
        >
            <h2
                class="font-weight-bold"
                style="font-size: 4rem; text-align: center"
            >
                Ready to Get Started?
            </h2>
            <p style="text-align: center">
                Join hundreds of companies already using the OJT Management
                System to streamline their training programs.
            </p>

            <Link
                :href="route('login')"
                class="get-started-btn"
                style="margin-top: 139px"
            >
                Sign In Today
            </Link>
        </section>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col text-start">
                        <h5>About Us</h5>
                        <a href="#" class="text-white">Learn More</a>
                    </div>
                    <div class="col text-center">
                        <h5>Contact Us</h5>
                        <a href="#" class="text-white">Email</a><br />
                        <a href="#" class="text-white">Phone</a>
                    </div>
                    <div class="col text-end">
                        <h5>Privacy Policy</h5>
                        <a href="#" class="text-white">View Policy</a><br />
                        <h5>Terms of Service</h5>
                        <a href="#" class="text-white">Read Terms</a>
                    </div>
                </div>
                <p class="text-center mt-3">
                    &copy; 2024 OJT Management System. All rights reserved.
                </p>
            </div>
        </footer>
    </body>
 
</template>

<style scoped>

.header {
    height: 150px;
    display: flex;
    align-items: center;
    padding: 0 100px;
    background-color: #ffffff;
}
.logo-title {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}
.logo {
    width: 60px;
    height: 60px;
    margin-right: 10px;
}
.title {
    font-size: 1.5rem;
    font-weight: normal;
}

.search-bar {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.search-input-group {
    display: flex;
    align-items: center;
    background-color: #f0f6ed;
    border: 2px solid #13ac34;
    border-radius: 50px;
    padding: 5px 15px;
    max-width: 500px;
    width: 100%;
}

.search-input-group .search-icon {
    color: #000000;
    margin-right: 10px;
    font-size: 1rem;
}

.search-input {
    border: none;
    background-color: transparent;
    outline: none;
    width: 100%;
}

.notification-icon {
    font-size: 1.5rem;
    color: #036719;
    cursor: pointer;
}

.nav-buttons {
    display: flex;
    gap: 15px;
    flex-shrink: 0;
}

.nav-buttons a {
    color: #000000;
    text-decoration: none;
    font-weight: normal;
}

.hero-section {
    height: 875px;
    background-color: #f6c231;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: left;
    justify-content: center;
    color: #000000;
    padding: 0 100px;
    flex-direction: column;
}

.hero-section::before {
    content: "";
    background-image: url("/assets/bg.jpg");
    background-size: cover;
    background-position: center;
    opacity: 0.2;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: left;
    max-width: 500px;
    margin-bottom: 100px;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: normal;
    margin-bottom: 50px;
}

.hero-subtitle {
    font-size: 1.25rem;
    padding-left: 20px;
    max-width: 400px;
}

.get-started-btn {
    display: flex; /* Flexbox for centering */
    justify-content: center; /* Horizontally center content */
    align-items: center; /* Vertically center content */
    background-color: #2c6929;
    color: white;
    border: none;
    border-radius: 50px;
    width: 298px;
    height: 77px;
    font-size: 1.25rem;
    cursor: pointer;
    margin: 20px 0;
    z-index: 2;
    text-align: center;
    text-decoration: none; /* Ensure link text doesn't have an underline */
    align-self: center;
}

.get-started-btn:hover {
    background-color: #24551e;
}

.features-section {
    height: 875px;
    padding: 50px 300px;
    background-color: #ffffff;
}

.feature {
    margin-bottom: 60px;
    text-align: center;
}

.feature-title {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.feature .card {
    width: 300px;
    height: 300px;
    background-color: #f0f6ed;
    padding: 20px;
    border-radius: 50%;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.feature .card:hover {
    transform: scale(1.05);
}

.how-it-works-section {
    height: 875px;
    padding: 50px 100px;
    background-color: #f7ce5a;
}

.how-it-works {
    margin-bottom: 30px;
    text-align: center;
}

.how-it-works-title {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.how-it-works-card {
    background-color: #ffffff;
    border-radius: 50px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.ready-to-get-started {
    height: 875px;
    padding: 50px 100px;
    background-color: #ffffff;
    text-align: center;
}

.footer {
    background-color: #2c6929;
    color: white;
    padding: 20px 100px;
    text-align: center;
}
</style>
