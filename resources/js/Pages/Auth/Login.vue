<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};

// Navigate back to the previous page
const goBack = () => {
  history.back();
};
</script>

<template>
  <Head title="Log in" />
<body>
  <div class="container">
    <div class="left">

      <img src="/assets/logo.jpg" alt="Logo" class="logo" />
      <div class="title">Login</div>
   
    </div>

    <div class="right">
        <!-- Back Button -->
        <div class="back-button">
          <button @click="goBack" class="back-button-style">← Back</button>
        </div>
      <form @submit.prevent="submit" class="form-container">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email"
            v-model="form.email"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            v-model="form.password"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="remember-forgot-container">
          <div class="remember-me">
            <Checkbox name="remember" v-model:checked="form.remember" id="remember" />
            <label for="remember">Remember Me</label>
          </div>
          <div class="forgot-password">
            <Link v-if="canResetPassword" :href="route('password.request')">
              Forgot Password?
            </Link>
          </div>
        </div>

        <button
          type="submit"
          class="login-button"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Login
        </button>
      </form>
    </div>
  </div>
</body>
</template>

<style scoped>
/* Back Button Styling */
.back-button {
  width: 100%;
  display: flex;
  justify-content: flex-start;
  margin-bottom: 20px;
}

.back-button-style {
  background: none;
  border: none;
  color: #2c6929;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  text-decoration: none;
  transition: color 0.3s;
}

.back-button-style:hover {
  color: #0a751f;
}
/* General Styling */
body {
  font-family: 'Itim', cursive;
  background-color: #f0f0f0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

/* Main Container */
.container {
  display: flex;
  width: 90%;
  max-width: 1142px;
  height: 513px;
 

}

/* Left Section */
.left {
  width: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f6c231;
  padding: 20px;
  position: relative;
  
}

.left::before {
  content: '';
  background-image: url('/assets/bg.jpg');
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
  opacity: 0.2;
}

.logo {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  z-index: 2;
}

.title {
  margin-top: 15px;
  font-size: 28px;
  color: #000;
  z-index: 2;
}

/* Right Section */
.right {
  width: 50%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px;
  background-color: white;
}

.form-container {
  width: 100%;
  max-width: 400px;
}

/* Form Fields */
.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-size: 14px;
}

.form-group input {
  width: 100%;
  height: 40px;
  padding: 10px;
  border: 2px solid #13ac34;
  border-radius: 20px;
  font-size: 16px;
  background-color: #f0f6ed;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #0a751f;
}

/* Remember Me and Forgot Password */
.remember-forgot-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.remember-me {
  display: flex;
  align-items: center;
}

.remember-me input {
  margin-right: 5px;
}

.forgot-password a {
  color: #000;
  text-decoration: none;
  transition: color 0.3s;
}

.forgot-password a:hover {
  color: #424242;
}

/* Submit Button */
.login-button {
  width: 100%;
  height: 50px;
  border-radius: 25px;
  background-color: #2c6929;
  color: white;
  font-size: 18px;
  font-weight: bold;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s;
}

.login-button:hover {
  background-color: #13ac34;
}
</style>
