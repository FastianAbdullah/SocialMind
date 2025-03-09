<template>
    <div class="container-fluid p-4">
      <!-- Main Grid -->
      <div class="row">
        <!-- Engagement Overview Card -->
        <div class="col-xl-8 col-12 mb-4">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Engagement Overview</h5>
              <select v-model="selectedPeriod" class="form-select form-select-sm" style="width: auto">
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
              </select>
            </div>
            <div class="card-body">
              <div v-if="isLoading" class="d-flex justify-content-center align-items-center" style="height: 350px">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <div v-else id="engagementChart" style="height: 350px"></div>
            </div>
          </div>
        </div>
  
        <!-- Stats and Upcoming Posts -->
        <div class="col-xl-4 col-12">
          <!-- Total Posts Card -->
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h2 class="mb-1">{{ totalPosts }}</h2>
                  <h6 class="text-muted">Total Posts</h6>
                  <p class="mb-0">
                    <i class="fas fa-arrow-up me-1 text-success"></i>
                    <span class="text-success">+{{ postsGrowth }}%</span> from last period
                  </p>
                </div>
                <div class="bg-primary p-3 rounded">
                  <i class="fas fa-calendar fa-2x text-white"></i>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Upcoming Posts Card -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Upcoming Posts</h6>
            </div>
            <div class="card-body">
              <div v-if="upcomingPosts.length" class="upcoming-posts">
                <div v-for="post in upcomingPosts" :key="post.id" 
                     class="upcoming-post-item p-3 bg-light rounded mb-2 hover-shadow">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-medium">{{ post.platform }}</span>
                    <span class="text-muted small">
                      <i class="fas fa-clock me-1"></i>
                      {{ formatDate(post.scheduledDate) }}
                    </span>
                  </div>
                  <p class="mb-0 text-secondary">{{ post.title }}</p>
                </div>
              </div>
              <div v-else class="text-center py-3">
                <p class="mb-0">No upcoming posts scheduled</p>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Platform Stats -->
        <div class="col-12 mt-4">
          <div class="row">
            <div v-for="platform in platforms" :key="platform.name" class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100 platform-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h3 class="mb-1">{{ platform.followers }}</h3>
                      <h6 class="text-muted">{{ platform.name }} Followers</h6>
                      <p class="mb-0">
                        <i :class="[
                          'fas',
                          platform.growth >= 0 ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger',
                          'me-1'
                        ]"></i>
                        <span :class="platform.growth >= 0 ? 'text-success' : 'text-danger'">
                          {{ Math.abs(platform.growth) }}%
                        </span> from last month
                      </p>
                    </div>
                    <div :class="['rounded p-3', platform.bgColor]">
                      <i :class="['fab', platform.icon, 'fa-2x text-white']"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Engagement Stats -->
        <div class="col-12">
          <div class="row">
            <div v-for="stat in engagementStats" :key="stat.title" class="col-md-4 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div :class="['rounded-circle p-3 me-3', stat.bgColor]">
                      <i :class="['fas', stat.icon, stat.iconColor]"></i>
                    </div>
                    <div>
                      <h3 class="mb-1">{{ stat.value }}</h3>
                      <h6 class="text-muted mb-0">{{ stat.title }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue';
  import ApexCharts from 'apexcharts';
  import { format } from 'date-fns';
  
  // State
  const selectedPeriod = ref(7);
  const isLoading = ref(false);
  const totalPosts = ref(156);
  const postsGrowth = ref(12.5);
  let chart = null;
  
  // Platform data
  const platforms = ref([
    { 
      name: 'Facebook', 
      followers: '10.5K', 
      growth: 5.2,
      bgColor: 'bg-primary',
      icon: 'fa-facebook-f'
    },
    { 
      name: 'Instagram', 
      followers: '15.8K', 
      growth: 8.4,
      bgColor: 'bg-danger',
      icon: 'fa-instagram'
    },
    { 
      name: 'LinkedIn', 
      followers: '8.2K', 
      growth: -2.1,
      bgColor: 'bg-info',
      icon: 'fa-linkedin-in'
    },
    { 
      name: 'Twitter', 
      followers: '12.1K', 
      growth: 3.8,
      bgColor: 'bg-dark',
      icon: 'fa-twitter'
    }
  ]);
  
  const upcomingPosts = ref([
    {
      id: 1,
      title: 'Product Launch Announcement',
      platform: 'Facebook',
      scheduledDate: '2024-02-25T10:00:00'
    },
    {
      id: 2,
      title: 'Behind the Scenes Video',
      platform: 'Instagram',
      scheduledDate: '2024-02-26T15:30:00'
    }
  ]);
  
  const engagementStats = ref([
    {
      title: 'Total Likes',
      value: '1.2K',
      icon: 'fa-heart',
      iconColor: 'text-danger',
      bgColor: 'bg-danger-subtle'
    },
    {
      title: 'Comments',
      value: '845',
      icon: 'fa-comment',
      iconColor: 'text-primary',
      bgColor: 'bg-primary-subtle'
    },
    {
      title: 'Shares',
      value: '324',
      icon: 'fa-share',
      iconColor: 'text-success',
      bgColor: 'bg-success-subtle'
    }
  ]);
  
  // Methods
  const formatDate = (date) => {
    return format(new Date(date), 'MMM dd, HH:mm');
  };
  
  const generateChartData = (days) => {
    return Array.from({ length: days }, (_, i) => ({
      date: new Date(Date.now() - (days - 1 - i) * 24 * 60 * 60 * 1000)
        .toLocaleDateString('en-US', { weekday: 'short' }),
      likes: Math.floor(Math.random() * 50) + 30,
      comments: Math.floor(Math.random() * 20) + 10,
      shares: Math.floor(Math.random() * 15) + 5
    }));
  };
  
  const initChart = (data) => {
    const options = {
      series: [
        {
          name: 'Likes',
          data: data.map(item => item.likes)
        },
        {
          name: 'Comments',
          data: data.map(item => item.comments)
        },
        {
          name: 'Shares',
          data: data.map(item => item.shares)
        }
      ],
      chart: {
        height: 350,
        type: 'line',
        toolbar: {
          show: false
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800
        }
      },
      colors: ['#dc3545', '#0d6efd', '#198754'],
      stroke: {
        curve: 'smooth',
        width: 2
      },
      grid: {
        borderColor: '#e7e7e7',
        padding: {
          right: 30,
          left: 20
        }
      },
      markers: {
        size: 4,
        hover: {
          size: 6
        }
      },
      xaxis: {
        categories: data.map(item => item.date),
        labels: {
          style: {
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        labels: {
          formatter: (value) => Math.round(value)
        }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'right'
      },
      tooltip: {
        theme: 'light',
        x: {
          show: true
        }
      }
    };
  
    if (chart) {
      chart.destroy();
    }
    
    chart = new ApexCharts(document.querySelector("#engagementChart"), options);
    chart.render();
  };
  
  // Watchers
  watch(selectedPeriod, async (newPeriod) => {
    isLoading.value = true;
    // Simulate API call
    setTimeout(() => {
      const data = generateChartData(newPeriod);
      initChart(data);
      isLoading.value = false;
    }, 500);
  });
  
  // Lifecycle hooks
  onMounted(() => {
    const initialData = generateChartData(selectedPeriod.value);
    initChart(initialData);
  });
  </script>
  
  <style scoped>
  .platform-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .platform-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  .upcoming-post-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  
  .upcoming-post-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  .hover-shadow {
    transition: box-shadow 0.2s ease;
  }
  
  .hover-shadow:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  </style>