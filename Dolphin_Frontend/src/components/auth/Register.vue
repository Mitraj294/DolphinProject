<template>
  <div class="login-bg">
    <Toast />
    <img
      src="@/assets/images/Lines.svg"
      alt="Lines"
      class="bg-lines"
    />
    <img
      src="@/assets/images/Image.svg"
      alt="Illustration"
      class="bg-illustration"
    />
    <div class="login-card">
      <h2 class="login-title">Create Account</h2>
      <p class="login-subtitle">Register a new account</p>
      <!-- Step 1: Basic Info -->
      <form
        v-if="step === 1"
        @submit.prevent="goToStep2"
      >
        <div class="input-group name-group">
          <span class="icon"><i class="fas fa-user"></i></span>
          <input
            type="text"
            v-model="first_name"
            placeholder="First Name"
            ref="firstNameInput"
            required
          />
        </div>
        <div class="input-group name-group">
          <span class="icon"><i class="fas fa-user"></i></span>
          <input
            type="text"
            v-model="last_name"
            placeholder="Last Name"
            required
          />
        </div>
        <div class="input-group email-group">
          <span class="icon"><i class="fas fa-envelope"></i></span>
          <input
            type="email"
            v-model="email"
            placeholder="Email ID"
            required
          />
        </div>
        <div class="input-group phone-group">
          <span class="icon"><i class="fas fa-phone"></i></span>
          <input
            type="tel"
            v-model="phone"
            placeholder="Phone Number"
            required
          />
        </div>
        <button
          type="submit"
          class="login-btn"
        >
          Next
        </button>
      </form>

      <!-- Step 2: Organization Info -->
      <form
        v-else-if="step === 2"
        @submit.prevent="goToStep3"
      >
        <div class="input-group org-name-group">
          <span class="icon"><i class="fas fa-building"></i></span>
          <input
            type="text"
            v-model="organization_name"
            placeholder="Organization Name"
            ref="orgNameInput"
            required
          />
        </div>
        <div class="input-group org-size-group styled-select">
          <span class="icon"><i class="fas fa-users"></i></span>
          <select
            v-model="organization_size"
            required
          >
            <option
              disabled
              value=""
            >
              Select Organization Size
            </option>
            <option>250+ Employees (Large)</option>
            <option>100-249 Employees (Medium)</option>
            <option>1-99 Employees (Small)</option>
          </select>
          <span class="icon right"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="input-group org-address-group">
          <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
          <input
            type="text"
            v-model="organization_address"
            placeholder="Organization Address"
            required
          />
        </div>
        <div class="input-group org-city-group styled-select">
          <span class="icon"><i class="fas fa-city"></i></span>
          <select
            v-model="organization_city"
            required
          >
            <option
              disabled
              value=""
            >
              Select City
            </option>
            <option>A'bad</option>
            <option>Baroda</option>
            <option>Surat</option>
          </select>
          <span class="icon right"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="input-group org-state-group styled-select">
          <span class="icon"><i class="fas fa-flag"></i></span>
          <select
            v-model="organization_state"
            required
          >
            <option
              disabled
              value=""
            >
              Select State
            </option>
            <option>Gujarat</option>
            <option>UP</option>
            <option>MP</option>
          </select>
          <span class="icon right"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="input-group org-country-group styled-select">
          <span class="icon"><i class="fas fa-globe"></i></span>
          <select
            v-model="country"
            required
          >
            <option
              disabled
              value=""
            >
              Select Country
            </option>
            <option>India</option>
            <option>United States</option>
            <option>Canada</option>
          </select>
          <span class="icon right"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="input-group org-findus-group">
          <span class="icon"><i class="fas fa-question-circle"></i></span>
          <input
            type="text"
            v-model="find_us"
            placeholder="How did you find us?"
          />
        </div>
        <div class="input-group org-zip-group">
          <span class="icon"><i class="fas fa-mail-bulk"></i></span>
          <input
            type="text"
            v-model="organization_zip"
            placeholder="Zip Code"
            required
          />
        </div>
        <button
          type="button"
          class="login-btn"
          @click="goToStep1"
          style="margin-bottom: 8px"
        >
          Back
        </button>
        <button
          type="submit"
          class="login-btn"
        >
          Next
        </button>
      </form>

      <!-- Step 3: Password -->
      <form
        v-else-if="step === 3"
        @submit.prevent="handleRegister"
      >
        <div class="input-group password-group">
          <span class="icon"><i class="fas fa-lock"></i></span>
          <input
            :type="showPassword ? 'text' : 'password'"
            v-model="password"
            placeholder="Password"
            required
          />
          <span
            class="icon right"
            @click="showPassword = !showPassword"
            style="user-select: none"
          >
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </span>
        </div>
        <div class="input-group password-group">
          <span class="icon"><i class="fas fa-lock"></i></span>
          <input
            :type="showConfirmPassword ? 'text' : 'password'"
            v-model="confirm_password"
            placeholder="Confirm Password"
            required
          />
          <span
            class="icon right"
            @click="showConfirmPassword = !showConfirmPassword"
            style="user-select: none"
          >
            <i
              :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
            ></i>
          </span>
        </div>
        <button
          type="button"
          class="login-btn"
          @click="goToStep2"
          style="margin-bottom: 8px"
        >
          Back
        </button>
        <button
          type="submit"
          class="login-btn"
        >
          Register
        </button>
      </form>
      <div class="switch-auth">
        <span>Already have an account?</span>
        <router-link
          to="/login"
          class="switch-link"
          >Login here</router-link
        >
      </div>
      <div class="footer">
        <img
          src="@/assets/images/Logo.svg"
          alt="Dolphin Logo"
          class="footer-logo"
        />
        <p class="copyright">
          &copy; {{ currentYear }} All Rights Reserved By Dolphin
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const API_BASE_URL =
  process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

export default {
  name: 'Register',
  components: { Toast },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      step: 1,
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      password: '',
      confirm_password: '',
      organization_name: '',
      organization_size: '',
      organization_address: '',
      organization_city: '',
      organization_state: '',
      organization_zip: '',
      country: '',
      currentYear: new Date().getFullYear(),
      find_us: '',
      showPassword: false,
      showConfirmPassword: false,
    };
  },
  methods: {
    goToStep3() {
      this.step = 3;
    },
    goToStep2() {
      this.step = 2;
      this.$nextTick(() => {
        setTimeout(() => {
          if (this.$refs.orgNameInput) this.$refs.orgNameInput.focus();
        }, 50);
      });
    },
    goToStep1() {
      this.step = 1;
      this.$nextTick(() => {
        setTimeout(() => {
          if (this.$refs.firstNameInput) this.$refs.firstNameInput.focus();
        }, 50);
      });
    },
    async handleRegister() {
      try {
        const response = await axios.post(`${API_BASE_URL}/api/register`, {
          first_name: this.first_name,
          last_name: this.last_name,
          email: this.email,
          phone: this.phone,
          password: this.password,
          confirm_password: this.confirm_password,
          org_name: this.organization_name,
          org_size: this.organization_size,
          address: this.organization_address,
          city: this.organization_city,
          state: this.organization_state,
          zip: this.organization_zip,
          country: this.country,
          find_us: this.find_us,
        });
        if (response.status === 201) {
          this.$router.push({
            name: 'Login',
            query: {
              email: this.email,
              registrationSuccess: true,
            },
          });
        }
      } catch (error) {
        console.error('Registration failed:', error);
        let errorMessage = 'Registration failed. Please try again.';
        if (
          error.response &&
          error.response.data &&
          error.response.data.message
        ) {
          errorMessage = error.response.data.message;
        } else if (
          error.response &&
          error.response.data &&
          error.response.data.errors
        ) {
          const errors = error.response.data.errors;
          errorMessage = Object.values(errors).flat().join(' ');
        }
        this.toast.add({
          severity: 'error',
          summary: 'Registration Error',
          detail: errorMessage,
          life: 4000,
        });
      }
    },
    async prefillFromLead() {
      // Try to get lead data from query params or API
      const params = this.$route.query;
      // If all data is in query params, use them (fallback)
      let prefilled = false;
      if (
        params.first_name ||
        params.last_name ||
        params.phone ||
        params.organization_name ||
        params.find_us
      ) {
        if (params.first_name) this.first_name = params.first_name;
        if (params.last_name) this.last_name = params.last_name;
        if (params.email) this.email = params.email;
        if (params.phone) this.phone = params.phone;
        if (params.organization_name)
          this.organization_name = params.organization_name;
        if (params.organization_size)
          this.organization_size = params.organization_size;
        if (params.organization_address)
          this.organization_address = params.organization_address;
        if (params.organization_city)
          this.organization_city = params.organization_city;
        if (params.organization_state)
          this.organization_state = params.organization_state;
        if (params.organization_zip)
          this.organization_zip = params.organization_zip;
        if (params.find_us) this.find_us = params.find_us;
        prefilled = true;
      }
      // Always try backend prefill if email, lead_id, or token is present
      if (params.lead_id || params.token || params.email) {
        try {
          const res = await axios.get(`${API_BASE_URL}/api/leads/prefill`, {
            params: {
              lead_id: params.lead_id,
              token: params.token,
              email: params.email,
            },
          });
          if (res.data && res.data.lead) {
            const lead = res.data.lead;
            this.first_name = lead.first_name || '';
            this.last_name = lead.last_name || '';
            this.email = lead.email || '';
            this.phone = lead.phone || '';
            this.organization_name = lead.organization_name || '';
            this.organization_size = lead.organization_size || '';
            this.organization_address = lead.organization_address || '';
            this.organization_city = lead.organization_city || '';
            this.organization_state = lead.organization_state || '';
            this.organization_zip = lead.organization_zip || '';
            this.find_us = lead.find_us || '';
            prefilled = true;
          }
        } catch (e) {
          // fallback to query params if API fails
        }
      }
      // Fallback: prefill from query params if backend prefill did not work
      if (!prefilled) {
        if (params.first_name) this.first_name = params.first_name;
        if (params.last_name) this.last_name = params.last_name;
        if (params.email) this.email = params.email;
        if (params.phone) this.phone = params.phone;
        if (params.organization_name)
          this.organization_name = params.organization_name;
        if (params.organization_size)
          this.organization_size = params.organization_size;
        if (params.organization_address)
          this.organization_address = params.organization_address;
        if (params.organization_city)
          this.organization_city = params.organization_city;
        if (params.organization_state)
          this.organization_state = params.organization_state;
        if (params.organization_zip)
          this.organization_zip = params.organization_zip;
        if (params.find_us) this.find_us = params.find_us;
      }
    },
  },
  mounted() {
    this.prefillFromLead();
    this.$nextTick(() => {
      setTimeout(() => {
        if (this.$refs.firstNameInput) this.$refs.firstNameInput.focus();
      }, 50);
    });
  },
};
</script>

<style scoped>
select {
  width: 100%;
  padding: 12px 12px 12px 48px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  color: #222;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.18s;
  background: #fff;
  appearance: none;
  margin-bottom: 0;
}
.styled-select {
  position: relative;
}
.styled-select .icon {
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}
.login-bg {
  position: relative;
  width: 100vw;
  height: 100vh;
  background: #f8f9fb;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

.bg-lines {
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
  height: auto;
  z-index: 0;
}

.bg-illustration {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 300px;
  height: auto;
  z-index: 0;
}

.login-card {
  position: relative;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  padding: 48px 48px 32px 48px;
  text-align: center;
  z-index: 1;
  max-width: 480px;
  width: 100%;
  box-sizing: border-box;
}

.login-title {
  font-size: 2rem;
  font-weight: 600;
  color: #234056;
  margin-bottom: 8px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}

.login-subtitle {
  font-size: 1rem;
  color: #787878;
  margin-bottom: 32px;
  font-family: 'Inter', Arial, sans-serif;
}

.input-group {
  position: relative;
  margin-bottom: 24px;
}
.input-group input {
  width: 100%;
  padding: 12px 12px 12px 48px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  color: #222;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.18s;
}
.input-group input:focus {
  border-color: #0074c2;
}
.input-group .icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #787878;
  font-size: 1rem;
}
.input-group .icon.right {
  left: auto;
  right: 16px;
  cursor: pointer;
}

.login-btn {
  width: 100%;
  padding: 14px;
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 32px;
  margin-top: 8px;
  transition: background 0.2s;
}
.login-btn:hover {
  background: #1690d1;
}

.switch-auth {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  font-size: 1rem;
  color: #787878;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.switch-link {
  color: #0164a5;
  text-decoration: underline;
  cursor: pointer;
  font-weight: 500;
}
.switch-link:hover {
  color: #1690d1;
}

.footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 8px;
}
.footer-logo {
  width: 28px;
  height: 28px;
  object-fit: contain;
  margin-bottom: 10px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
}
.copyright {
  color: #787878;
  font-size: 14px;
  font-family: 'Inter', Arial, sans-serif;
  text-align: center;
  margin-top: 4px;
}

@media (max-width: 1200px) {
  .bg-lines {
    width: 180px;
    left: 1vw;
    top: 8vh;
  }
  .bg-illustration {
    width: 220px;
    right: 1vw;
    bottom: 8vh;
  }
  .login-card {
    padding: 32px;
    max-width: 400px;
  }
}

@media (max-width: 768px) {
  .bg-lines {
    width: 120px;
    left: -20px;
    top: -20px;
  }
  .bg-illustration {
    width: 150px;
    right: -20px;
    bottom: -20px;
  }
  .login-card {
    padding: 24px;
    margin: 0 16px;
  }
  .login-title {
    font-size: 1.8rem;
  }
  .login-subtitle {
    font-size: 0.9rem;
  }
  .input-group input {
    font-size: 0.9rem;
  }
  .login-btn {
    font-size: 1rem;
    padding: 12px;
  }
}
</style>
