<template>
    <div class="container-fluid p-4">
      <div class="row g-4">
        <!-- Main Stats Row -->
        <div class="col-12">
          <div class="row g-4">
            <!-- Total Posts Card -->
            <div class="col-xl-3 col-md-6">
              <div class="card h-100 stat-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h2 class="mb-1">{{ totalPosts }}</h2>
                      <h6 class="text-muted">Total Posts</h6>
                      <p class="mb-0">
                        <i class="fas fa-arrow-up me-1 text-success"></i>
                        <span class="text-success">+{{ postsGrowth }}%</span>
                      </p>
                    </div>
                    <div class="bg-primary p-3 rounded">
                      <i class="fas fa-calendar fa-2x text-white"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Engagement Card -->
            <div class="col-xl-3 col-md-6">
              <div class="card h-100 stat-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h2 class="mb-1">45.2K</h2>
                      <h6 class="text-muted">Total Engagement</h6>
                      <p class="mb-0">
                        <i class="fas fa-arrow-up me-1 text-success"></i>
                        <span class="text-success">+8.3%</span>
                      </p>
                    </div>
                    <div class="bg-success p-3 rounded">
                      <i class="fas fa-chart-line fa-2x text-white"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reach Card -->
            <div class="col-xl-3 col-md-6">
              <div class="card h-100 stat-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h2 class="mb-1">125K</h2>
                      <h6 class="text-muted">Total Reach</h6>
                      <p class="mb-0">
                        <i class="fas fa-arrow-up me-1 text-success"></i>
                        <span class="text-success">+12.4%</span>
                      </p>
                    </div>
                    <div class="bg-info p-3 rounded">
                      <i class="fas fa-users fa-2x text-white"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Conversion Card -->
            <div class="col-xl-3 col-md-6">
              <div class="card h-100 stat-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h2 class="mb-1">3.8%</h2>
                      <h6 class="text-muted">Conversion Rate</h6>
                      <p class="mb-0">
                        <i class="fas fa-arrow-down me-1 text-danger"></i>
                        <span class="text-danger">-1.2%</span>
                      </p>
                    </div>
                    <div class="bg-warning p-3 rounded">
                      <i class="fas fa-chart-pie fa-2x text-white"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
           <!-- Platform Stats -->
           <div class="col-12">
            <div class="row g-4">
              <div v-for="platform in platforms" :key="platform.name" class="col-xl-3 col-md-6">
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

        <!-- Main Chart and Upcoming Posts -->
        <div class="col-12">
          <div class="row g-4">
            <!-- Main Chart -->
            <div class="col-xl-8 col-lg-7">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
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
                  <div v-else-if="chartError" class="d-flex flex-column justify-content-center align-items-center" style="height: 350px">
                    <i class="fas fa-exclamation-triangle text-danger mb-2 fa-2x"></i>
                    <p class="text-danger mb-3">Failed to load chart</p>
                    <button @click="renderChartWithRetry" class="btn btn-sm btn-primary">
                      <i class="fas fa-sync-alt me-1"></i> Retry
                    </button>
                  </div>
                  <div v-else id="engagementChart" style="height: 350px"></div>
                </div>
              </div>
            </div>

            <!-- Upcoming Posts -->
            <div class="col-xl-4 col-lg-5">
              <div class="card h-100">
                <div class="card-header py-3">
                  <h5 class="mb-0">Upcoming Posts</h5>
                </div>
                <div class="card-body p-0">
                  <div v-if="upcomingPosts.length" class="upcoming-posts p-3">
                    <div v-for="post in upcomingPosts" :key="post.id" 
                         class="upcoming-post-item p-3 bg-light rounded mb-3 hover-shadow">
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
                  <div v-else class="text-center py-4">
                    <p class="mb-0 text-muted">No upcoming posts scheduled</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

     

        <!-- Trends Chart -->
        <!-- <div class="col-12">
          <div class="card">
            <div class="card-header py-3">
              <h5 class="mb-0">Engagement Trends</h5>
            </div>
            <div class="card-body">
              <div id="trendsChart" style="height: 300px"></div>
            </div>
          </div>
        </div> -->

        <!-- Engagement Stats -->
        <div class="col-12">
          <div class="row g-4">
            <div v-for="stat in engagementStats" :key="stat.title" class="col-md-4">
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
  import { ref, onMounted, watch, onDeactivated } from 'vue';
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
        const chartElement = document.querySelector("#engagementChart");
      if (!chartElement) {
        console.error('Chart container not found');
        return;
      }
      if (!document.contains(document.querySelector("#engagementChart"))) {
    console.error('Chart container not in DOM');
    return;
  }

      // Destroy previous chart if exists
      if (chart) {
        chart.destroy();
      }

      // Check if ApexCharts is available
      if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts not loaded');
        return;
      }
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
    
    try {
    chart = new ApexCharts(chartElement, options);
    chart.render();
  } catch (error) {
    console.error('Chart initialization failed:', error);
  }
  };
  const renderChartWithRetry = async (attempt = 1) => {
  if (attempt > 3) {
    console.error('Max chart render attempts reached');
    return;
  }

  try {
    const initialData = generateChartData(selectedPeriod.value);
    initChart(initialData);
  } catch (error) {
    console.warn(`Chart render attempt ${attempt} failed, retrying...`, error);
    await new Promise(resolve => setTimeout(resolve, 500 * attempt));
    renderChartWithRetry(attempt + 1);
  }
};

onMounted(() => {
  renderChartWithRetry();
});

watch(selectedPeriod, async (newPeriod) => {
  isLoading.value = true;
  await renderChartWithRetry();
  isLoading.value = false;
});
  
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
  
  onDeactivated(() => {
  if (chart) {
    chart.destroy();
    chart = null;
  }
});
  // Lifecycle hooks
  onMounted(() => {
    const initialData = generateChartData(selectedPeriod.value);
    initChart(initialData);
  });

  // Add trends chart instance
  let trendsChart = null;

  // Add trends chart initialization
  const initTrendsChart = () => {
    const trendsElement = document.querySelector("#trendsChart");
    if (!trendsElement) return;

    if (trendsChart) {
      trendsChart.destroy();
    }

    const options = {
      series: [
        {
          name: 'Likes',
          data: [28, 45, 35, 50, 32, 55, 23]
        },
        {
          name: 'Comments',
          data: [14, 25, 20, 25, 18, 30, 15]
        },
        {
          name: 'Shares',
          data: [8, 12, 10, 15, 8, 18, 8]
        }
      ],
      chart: {
        height: 300,
        type: 'area',
        toolbar: {
          show: false
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800
        }
      },
      colors: ['#0d6efd', '#198754', '#dc3545'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.3,
          stops: [0, 90, 100]
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      xaxis: {
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
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

    try {
      trendsChart = new ApexCharts(trendsElement, options);
      trendsChart.render();
    } catch (error) {
      console.error('Trends chart initialization failed:', error);
    }
  };

  // Modify your existing onMounted to include trends chart
  onMounted(() => {
    const initialData = generateChartData(selectedPeriod.value);
    initChart(initialData);
    initTrendsChart(); // Add trends chart initialization
  });

  // Modify your cleanup to include trends chart
  onDeactivated(() => {
    if (chart) {
      chart.destroy();
      chart = null;
    }
    if (trendsChart) {
      trendsChart.destroy();
      trendsChart = null;
    }
  });
  </script>
  
  <style scoped>
  .stat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
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
  
  /* Responsive adjustments */
  @media (max-width: 1200px) {
    .stat-card h2 {
      font-size: 1.5rem;
    }
  }
  
  @media (max-width: 768px) {
    .card-body {
      padding: 1rem;
    }
    
    .upcoming-posts {
      max-height: 300px;
      overflow-y: auto;
    }
  }
  </style>