<template>
  <Loader v-if="isLoading"></Loader>
  <template v-else>
    <CustomCursor></CustomCursor>
    <!-- Header 7 -->
    <div class="header-top pt-6 d-none d-lg-block transition position-absolute w-100 z-index-1">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-10">
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
              <a href="#" class="link d-inline-block py-1 px-2 rounded secondary-fixed on-secondary-fixed-variant ff-1 fs-12 lh-1 fw-bold text-center flex-shrink-0"> Limited Offer </a>
              <span class="d-block ff-1 fs-12 fw-semibold clr-white"> Sign up and receive 20% bonus discount on checkout. </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Header></Header>

    <!-- /Header 7 -->
    <section class="breadcrumb-section">
      <div class="breadcrumb-section-inner">
        <div class="container">
          <div class="row g-4 justify-content-center">
            <div class="col-xxl-5 col-xl-8">
              <div class="text-center">
                <div class="d-inline-flex align-items-center py-2 px-4 bg-info-10 bg-opacity-3 rounded-1">
                  <span class="fs-12 clr-white">Social Mind AI</span>
                </div>
                <h2 class="h3 fw-bold clr-neutral-87 mt-4 mb-0">Privacy and Policy</h2>
              </div>
            </div>
            <div class="col-xl-8">
              <div class="section-space-xsm-y">
                <h5 class="clr-neutral-90"> Introduction </h5>
                <p class="clr-neutral-80"> These terms and conditions outline the rules and regulations for the use of Social Mind's Website. </p>
                <p class="mb-6 clr-neutral-80"> We're passionate about empowering creatives with the tools they need to bring their vision to life that's why we've developed. Working globally with largest brands, We believe that safe. We combine ideas and behaviours, and insights with that customers love our services. </p>
                <ul class="list gap-6 mb-6">
                  <li class="welcome-list-1">
                    <div class="d-flex gap-3">
                      <span class="flex-shrink-0 d-grid place-content-center w-6 h-6 rounded-circle border border-primary-key border-opacity-3 bg-primary-key bg-opacity-2 clr-primary-key">
                        <i class="bi bi-check2"></i>
                      </span>
                      <span class="d-block clr-neutral-70"> AutoPoster on various platforms </span>
                    </div>
                  </li>
                  <li class="welcome-list-1">
                    <div class="d-flex gap-3">
                      <span class="flex-shrink-0 d-grid place-content-center w-6 h-6 rounded-circle border border-primary-key border-opacity-3 bg-primary-key bg-opacity-2 clr-primary-key">
                        <i class="bi bi-check2"></i>
                      </span>
                      <span class="d-block clr-neutral-70"> Content Schedular </span>
                    </div>
                  </li>
                  <li class="welcome-list-1">
                    <div class="d-flex gap-3">
                      <span class="flex-shrink-0 d-grid place-content-center w-6 h-6 rounded-circle border border-primary-key border-opacity-3 bg-primary-key bg-opacity-2 clr-primary-key">
                        <i class="bi bi-check2"></i>
                      </span>
                      <span class="d-block clr-neutral-70"> Content Optimization </span>
                    </div>
                  </li>
                  <li class="welcome-list-1">
                    <div class="d-flex gap-3">
                      <span class="flex-shrink-0 d-grid place-content-center w-6 h-6 rounded-circle border border-primary-key border-opacity-3 bg-primary-key bg-opacity-2 clr-primary-key">
                        <i class="bi bi-check2"></i>
                      </span>
                      <span class="d-block clr-neutral-70"> Content Optimization </span>
                    </div>
                  </li>
                  <li class="welcome-list-1">
                    <div class="d-flex gap-3">
                      <span class="flex-shrink-0 d-grid place-content-center w-6 h-6 rounded-circle border border-primary-key border-opacity-3 bg-primary-key bg-opacity-2 clr-primary-key">
                        <i class="bi bi-check2"></i>
                      </span>
                      <span class="d-block clr-neutral-70"> Sentinment Analysis </span>
                    </div>
                  </li>
                </ul>
                <p class="clr-neutral-80 mb-0"> Social Mind is committed to protecting your privacy. This Privacy Policy outlines how we collect,
                  use, and safeguard your information.   </p>
              </div>
              <div class="section-space-xsm-y">
                <h5 class="clr-neutral-90"> Information We Collect</h5>
                <p class="clr-neutral-80"> (a) Name, email address, and other contact information when you register an account. (b) Information on how you use our services, including interactions with social media platforms and analytics. (c) We use cookies to improve user experience and analyze traffic.</p>
              </div>
              <div class="section-space-xsm-y">
                <h5 class="clr-neutral-90"> Your content in our services </h5>
                <p class="clr-neutral-80"> (a) To provide and maintain our services. (b)  To personalize your experience and improve the functionality of Social Mind. (c) To analyze trends and optimize our platform. </p>
              </div>
              <div class="section-space-xsm-y">
                <h5 class="clr-neutral-90"> Data Protection </h5>
                <p class="clr-neutral-80">  Social Mind does not sell, trade, or share your personal information with third parties.</p>
                <p class="clr-neutral-80">  We implement security measures such as data encryption to protect your information from unauthorized access, disclosure, or alteration. However, no transmission over the internet is completely secure</p>
                <p class="clr-neutral-80 mb-0"> Our platform may link to third-party sites or services. Social Mind is not responsible for the privacy practices or content of these external sites </p>
                <p class="clr-neutral-80 mb-0"> We retain personal information for as long as your account is active or as needed to provide you services.</p>

              </div>
              <div class="section-space-xsm-y">
                <h5 class="clr-neutral-90"> Rights And Privacy Updates </h5>
                <p class="clr-neutral-80">  You have the right to request access to, correction, or deletion of your personal information. Contact us at privacy@socialmind.com for assistance</p>
                <p class="clr-neutral-80">  Social Mind reserves the right to update this Privacy Policy. Changes will be posted on this page, and continued use of the site implies acceptance</p>
                <p class="clr-neutral-80 mb-0"> If you have any questions or concerns about this Privacy Policy, please contact us at privacy@socialmind.com. </p>

              </div>
            </div>
          </div>
        </div>
      </div>
      <img src="../../../public/img/breadcrumb-shape-top.png" alt="image" class="img-fluid breadcrumb-shape breadcrumb-shape-top">
      <img src="../../../public/img/breadcrumb-shape-left.png" alt="image" class="img-fluid breadcrumb-shape breadcrumb-shape-left">
      <img src="../../../public/img/breadcrumb-shape-right.png" alt="image" class="img-fluid breadcrumb-shape breadcrumb-shape-right">
      <img src="../../../public/img/breadcrumb-shape-line-left.png" alt="image" class="img-fluid breadcrumb-shape breadcrumb-shape-line-left">
      <img src="../../../public/img/breadcrumb-shape-line-right.png" alt="image" class="img-fluid breadcrumb-shape breadcrumb-shape-line-right">
    </section>

    <!-- Footer Section -->
    <Footer></Footer>
  </template>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'

import Footer from "../components/Footer.vue";
import CustomCursor from "../components/CustomCursor.vue"
import Header from "../components/Header.vue"
import { ref, onMounted } from 'vue';
import { useDynamicCss } from "../composables/useDynamicCss";

// Data properties
const cssFiles = [
  'http://[::1]:5173/resources/css/fonts/ff-1.css',
  'http://[::1]:5173/resources/css/fonts/ff-3.css',
  'http://[::1]:5173/resources/css/fonts/bootstrap-icons.css',
  'http://[::1]:5173/resources/css/plugins.min.css',
  'http://[::1]:5173/resources/css/style.min.css'
];

const isLoading = ref(true)

const {removeDynamicCss, initializeCss } = useDynamicCss(isLoading,cssFiles);


onMounted(async () => {
  await initializeCss();
});
</script>