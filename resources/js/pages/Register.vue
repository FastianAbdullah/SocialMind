<template>
  <Loader v-if="isLoading"></Loader>
  <template v-else>
    <div class="login-page">
      <div class="container">
        <div class="row justify-content-center align-items-center">
          <div class="col-xxl-7 col-xl-6 d-none d-xl-block">
            <div class="radar-main-wrapper">
              <div class="radar-main-circle-one"></div>
              <div class="radar-main-circle-two"></div>
              <div class="radar-main-circle-three"></div>
              <img src="../../../public/img/login-shape-circle-text.png" alt="image"
                class="img-fluid radar-main-shape-text">
              <div class="radar-logo-wrapper">
                <div class="radar">
                  <div class="animated-text-wrapper">
                    <p class="cd-headline slide mb-0">
                      <span class="cd-words-wrapper">
                        <b class="is-hidden">Social Mind AI</b>
                        <b class="is-hidden">Social Mind AI</b>
                        <b class="is-visible">Social Mind AI</b>
                      </span>
                    </p>
                  </div>
                </div>
                <div class="logo-wrapper d-flex justify-content-center align-items-center">
                  <img src="../../../public/img/hero-7-logo.png" alt="image"
                    class="img-fluid hero-7-logo">
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-5 col-xl-6 col-lg-8 col-md-10">
            <div class="gradient-card py-sm-12 py-8 px-sm-8 px-5 rounded-5">
              <p class="fs-24 fw-medium clr-neutral-80 mb-5">Welcome !</p>
              <h4 class="h4 mb-2 clr-neutral-90 fw-bold">Sign up to </h4>
              <p class="clr-neutral-80">Get Started with 10,000 Free Words</p>
              <form @submit.prevent="register">
                <div class="mt-8">
                  <label class="clr-neutral-80 mb-2">Email</label>
                  <input v-model="form.email" type="email" name="email"
                    class="form-control border border-neutral-17 clr-neutral-90 :focus-clr-current rounded-2 py-4 px-4 bg-neutral-4 placeholder-50 focus-bg-none"
                    placeholder="Enter your email">
                  <div v-if="errors.email" class="text-danger mt-2">
                    <small v-for="error in errors.email" :key="error">{{ error }}</small>
                  </div>
                </div>
                <div class="mt-8">
                  <label class="clr-neutral-80 mb-2">User name</label>
                  <input v-model="form.name" type="text" name="name"
                    class="form-control border border-neutral-17 clr-neutral-90 :focus-clr-current rounded-2 py-4 px-4 bg-neutral-4 placeholder-50 focus-bg-none"
                    placeholder="Enter your user name">
                  <div v-if="errors.name" class="text-danger mt-2">
                    <small v-for="error in errors.name" :key="error">{{ error }}</small>
                  </div>
                </div>
                <div class="mt-8">
                  <label class="clr-neutral-80 mb-2">Password</label>
                  <div class="pass-field-area">
                    <input v-model="form.password" type="password" name="password"
                      class="form-control border border-neutral-17 clr-neutral-90 :focus-clr-current rounded-2 py-4 px-4 bg-neutral-4 placeholder-50 focus-bg-none"
                      placeholder="Enter your password">
                    <button type="button" class="bg-transparent border-0 pass-eye" @click="togglePassword('password')">
                      <i :class="showPassword.password ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill'"></i>
                    </button>
                  </div>
                  <div v-if="errors.password" class="text-danger mt-2">
                    <small v-for="error in errors.password" :key="error">{{ error }}</small>
                  </div>
                </div>
                <div class="mt-8">
                  <label class="clr-neutral-80 mb-2">Confirm Password</label>
                  <div class="pass-field-area">
                    <input v-model="form.password_confirmation" type="password" name="password_confirmation"
                      class="form-control border border-neutral-17 clr-neutral-90 :focus-clr-current rounded-2 py-4 px-4 bg-neutral-4 placeholder-50 focus-bg-none"
                      placeholder="Confirm password">
                    <button type="button" class="bg-transparent border-0 pass-eye" @click="togglePassword('password_confirmation')">
                      <i :class="showPassword.password_confirmation ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill'"></i>
                    </button>
                  </div>
                  <div v-if="errors.password_confirmation" class="text-danger mt-2">
                    <small v-for="error in errors.password_confirmation" :key="error">{{ error }}</small>
                  </div>
                </div>
                <button type="submit"
                  class="link d-inline-flex justify-content-center align-items-center gap-2 py-4 px-6 border border-primary-key bg-primary-key rounded-1 fw-bold clr-white border-0 w-100 mt-8 :arrow-btn">
                  <span>Register</span>
                  <i class="bi bi-arrow-right"></i>
                </button>
                <p class="mb-0 clr-neutral-80 text-center mt-8">
                  Do you have an Account?
                  <router-link to="/login" class="link clr-primary-key fw-semibold">Login Now</router-link>
                </p>
                <div class="text-center mt-6">
                  <router-link to="/login"
                    class="link d-inline-flex justify-content-center align-items-center gap-2 py-2 px-4 border border-primary-key bg-primary-key :bg-primary-30 rounded-pill fs-14 fw-bold text-center clr-white">
                    <span class="d-block">Back to Login</span>
                    <span class="d-block fs-10">
                      <i class="bi bi-arrow-up-right"></i>
                    </span>
                  </router-link>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="login-copyright">
          <p class="mb-0 text-center clr-neutral-80">Copyright @2023 <span class="clr-white">Social Mind</span>
          </p>
        </div>
      </div>
      <img src="../../../public/img/login-shape-top.png" alt="image" class="img-fluid login-shape login-shape-top">
      <img src="../../../public/img/login-shape-left.png" alt="image" class="img-fluid login-shape login-shape-left">
      <img src="../../../public/img/login-shape-right.png" alt="image"
        class="img-fluid login-shape login-shape-right">
      <img src="../../../public/img/login-shape-line-left.png" alt="image"
        class="img-fluid login-shape login-shape-line-left">
      <img src="../../../public/img/login-shape-line-right.png" alt="image"
        class="img-fluid login-shape login-shape-line-right">
    </div>
  </template>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import '../pages/Register.css';
import Loader from '../components/Loader.vue';
import { useDynamicResources } from "../composables/useDynamicResources";

// Data properties
const cssFiles = [
  'css/fonts/ff-1.css',
  'css/fonts/ff-3.css',
  'css/fonts/bootstrap-icons.css',
  'css/plugins.min.css',
  'css/style.min.css'
];

const isLoading = ref(true)

const { initializeCss } = useDynamicResources(isLoading,cssFiles);


onMounted(async () => {
  // await removeDynamicCss();
  await initializeCss();
});

const router = useRouter();

// Add this before the form data
const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

// Modify the form reactive object
const form = reactive({
  email: '',
  name: '',
  password: '',
  password_confirmation: '',
  timezone: timezone
});

// Show/hide password state using reactive
const showPassword = reactive({
  password: false,
  password_confirmation: false
});

// Errors state using ref
const errors = ref({});

// Register method
const register = async () => {
  try {
    const response = await axios.post('/register', form);
    console.log('Registration successful:', response.data);
    router.push('/login');
  } catch (error) {
    if (error.response && error.response.data) {
      errors.value = error.response.data.errors;
      console.error('Registration failed:', errors.value);
    } else {
      console.error('An unexpected error occurred:', error);
    }
  }
};

// Toggle password visibility
const togglePassword = (field) => {
  showPassword[field] = !showPassword[field];
  const inputField = document.querySelector(`[name="${field}"]`);
  inputField.type = showPassword[field] ? 'text' : 'password';
};

</script>

