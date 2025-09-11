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
      <p class="login-subtitle">Please fill in the details to register</p>

      <!-- Step 1: Basic Info -->
      <form
        v-if="step === 1"
        @submit.prevent="goToStep2"
      >
        <div style="margin-bottom: 32px;">
          <FormLabel>First Name</FormLabel>
          <div class="input-group name-group" style="margin-bottom: 0px !important">
            <span class="icon"><i class="fas fa-user"></i></span>
            <input
              type="text"
              v-model="first_name"
              placeholder="First Name"
              ref="firstNameInput"
              required
            />
            <FormLabel
              v-if="errors.first_name"
              class="error-message1
              "
              >{{ errors.first_name[0] }}</FormLabel
            >
          </div>
        </div>
        <div  style="margin-bottom: 32px" >
          <FormLabel>Last Name</FormLabel>
          <div class="input-group name-group"  style="margin-bottom: 0px !important">
            <span class="icon"><i class="fas fa-user"></i></span>
            <input
              type="text"
              v-model="last_name"
              placeholder="Last Name"
              ref="lastNameInput"
              required
            />
            <div >
              <FormLabel
                v-if="errors.last_name"
                class="error-message1
                "
                >{{ errors.last_name[0] }}</FormLabel>
            </div>
          </div>
        </div>
        <div style="margin-bottom: 32px;">
          <FormLabel>Email ID</FormLabel>
          <div class="input-group email-group" style="margin-bottom: 0px !important">
            <span class="icon"><i class="fas fa-envelope"></i></span>
            <input
              type="email"
              v-model="email"
              placeholder="Email ID"
              ref="emailInput"
              required
              aria-required="true"
            />
        
          </div>
              <div >
              <FormLabel
                v-if="errors.email"
                class="error-message1
                "
                >{{ errors.email[0] }}</FormLabel
              >
            </div>
        </div>
        <div  style="margin-bottom: 32px; ">
          <FormLabel>Phone Number</FormLabel>
          <div class="input-group phone-group " style="margin-bottom: 0px !important">
            <span class="icon"><i class="fas fa-phone"></i></span>
            <input
              type="tel"
              v-model="phone"
              placeholder="Phone Number"
              ref="phoneInput"
              required
            />
            <FormLabel
              v-if="errors.phone"
              class="error-message1
              "
              >{{ errors.phone[0] }}</FormLabel
            >
          </div>
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
        class="org-form"
      >
        <!-- Organization Name - Full Width -->
        <div class="form-row full-width">
          <div class="form-field">
            <FormLabel>Organization Name</FormLabel>
            <div class="input-group org-name-group">
              <span class="icon"><i class="fas fa-building"></i></span>
              <input
                type="text"
                v-model="organization_name"
                placeholder="Organization Name"
                ref="orgNameInput"
                required
              />   <div >
              <FormLabel
                v-if="errors.organization_name"
                class="error-message1
                "
                >{{ errors.organization_name[0] }}</FormLabel
              ></div>
            </div>
          </div>
        </div>

        <!-- Two Column Layout -->
        <div class="form-row two-columns">
          <div class="form-field">
            <FormLabel>Organization Size</FormLabel>
            <div class="input-group org-country-group styled-select">
              <FormDropdown
                v-model="organization_size"
                icon="fas fa-users"
                ref="orgSizeSelect"
                :options="[
                  {
                    value: '',
                    text: 'Select Organization Size',
                    disabled: true,
                  },
                  {
                    value: '250+ Employees (Large)',
                    text: '250+ Employees (Large)',
                  },
                  {
                    value: '100-249 Employees (Medium)',
                    text: '100-249 Employees (Medium)',
                  },
                  {
                    value: '1-99 Employees (Small)',
                    text: '1-99 Employees (Small)',
                  },
                ]"
              /><div>
              <FormLabel
                v-if="errors.organization_size"
                class="error-message1
                "
                >{{ errors.organization_size[0] }}</FormLabel
              >  </div>
            </div>
          </div>

          <div class="form-field">
            <FormLabel>How did you find us?</FormLabel>
            <div class="input-group org-findus-group">
              <FormDropdown
                v-model="find_us"
                icon="fas fa-search"
                ref="findUsSelect"
                :options="[
                  { value: null, text: 'Select', disabled: true },
                  ...(findUsOptions.length
                    ? findUsOptions.map((o) => ({ value: o, text: o }))
                    : [
                        { value: 'Google', text: 'Google' },
                        { value: 'Friend', text: 'Friend' },
                        { value: 'Colleague', text: 'Colleague' },
                        { value: 'Other', text: 'Other' },
                      ]),
                ]"
              />
              <FormLabel
                v-if="errors.find_us"
                class="error-message1
                "
                >{{ errors.find_us[0] }}</FormLabel
              >
            </div>
          </div>
        </div>

        <!-- Country, State, City Row -->
        <div class="form-row three-columns">
          <div class="form-field">
            <FormLabel>Country</FormLabel>
            <div class="input-group org-country-group styled-select">
              <FormDropdown
                v-model="country"
                icon="fas fa-globe"
                ref="countrySelect"
                :options="[
                  { value: null, text: 'Select', disabled: true },
                  ...countries.map((c) => ({ value: c.id, text: c.name })),
                ]"
                @change="onCountryChange"
                required
              /> <div >
              <FormLabel
                v-if="errors.country"
                class="error-message1
                "
                >{{ errors.country[0] }}</FormLabel
              >  </div> 
            </div>
          </div>

          <div class="form-field">
            <FormLabel>State</FormLabel>
            <div class="input-group org-state-group styled-select">
              <FormDropdown
                v-model="organization_state"
                icon="fas fa-flag"
                ref="stateSelect"
                :options="[
                  { value: null, text: 'Select', disabled: true },
                  ...states.map((s) => ({ value: s.id, text: s.name })),
                ]"
                @change="onStateChange"
                required
              /> <div >
              <FormLabel
                v-if="errors.state"
                class="error-message1
                "
                >{{ errors.state[0] }}</FormLabel
              > </div> 
            </div>
          </div>

          <div class="form-field">
            <FormLabel>City</FormLabel>
            <div class="input-group org-city-group styled-select">
              <FormDropdown
                v-model="organization_city"
                icon="fas fa-city"
                ref="citySelect"
                :options="[
                  { value: null, text: 'Select', disabled: true },
                  ...cities.map((city) => ({
                    value: city.id,
                    text: city.name,
                  })),
                ]"
                required
              /><div>
              <FormLabel
                v-if="errors.organization_city"
                class="error-message1
                "
                >{{ errors.organization_city[0] }}</FormLabel
              ></div>
            </div>
          </div>
        </div>

        <!-- Address and Zip Row -->
        <div class="form-row two-columns">
          <div class="form-field address-field">
            <FormLabel>Organization Address</FormLabel>
            <div class="input-group org-address-group">
              <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
              <input
                type="text"
                v-model="organization_address"
                placeholder="Organization Address"
                ref="orgAddressInput"
     
              />
              <div>
            <FormLabel
                v-if="errors.organization_address"
                class="error-message1
                "
                >{{ errors.organization_address[0] }}</FormLabel>
                </div>
            </div>
          </div>

          <div class="form-field zip-field">
            <FormLabel>Zip Code</FormLabel>
            <div class="input-group org-zip-group">
              <span class="icon"><i class="fas fa-mail-bulk"></i></span>
              <input
                type="text"
                v-model="organization_zip"
                placeholder="Zip Code"
                ref="orgZipInput"
           
              /><div>
              <FormLabel
                v-if="errors.organization_zip"
                class="error-message1
                "
                >{{ errors.organization_zip[0] }}</FormLabel
              ></div>
            </div>
          </div>
        </div>

        <!-- Buttons Row -->
        <div class="form-row full-width button-row">
          <button
            type="button"
            class="login-btn back-btn"
            @click="goToStep1"
          >
            Back
          </button>
          <button
            type="submit"
            class="login-btn next-btn"
          >
            Next
          </button>
        </div>
      </form>

      <!-- Step 3: Password -->
      <form
        v-else-if="step === 3"
        @submit.prevent="handleRegister"
      >
      <div style="margin-bottom: 32px;">
         <FormLabel>Password</FormLabel>
        <div class="input-group password-group"  style="margin-bottom: 0px !important" >
          <span class="icon"><i class="fas fa-lock"></i></span>
          <input
            :type="showPassword ? 'text' : 'password'"
            v-model="password"
            placeholder="Password"
            ref="passwordInput"
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
      <div>
          <FormLabel
            v-if="errors.password"
            class="error-message1
            "
            >{{ errors.password[0] }}</FormLabel
          ></div></div>

        <div style="margin-bottom: 32px;">
          <FormLabel>Confirm Password</FormLabel>
          <div class="input-group password-group"  style="margin-bottom: 0px !important">
            <span class="icon"><i class="fas fa-lock"></i></span>
            <input
              :type="showConfirmPassword ? 'text' : 'password'"
              v-model="confirm_password"
              placeholder="Confirm Password"
            ref="confirmPasswordInput"
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
        </div><div>
          <FormLabel
            v-if="errors.confirm_password"
            class="error-message1
            "
            >{{ errors.confirm_password[0] }}</FormLabel
          /></div></div>
       
        <button
          type="button"
          class="login-btn back-btn"
          @click="goToStep2"
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
import {
  FormLabel,
  FormDropdown,
  FormRow,
} from '@/components/Common/Common_UI/Form';

const API_BASE_URL =
  process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

export default {
  name: 'Register',
  components: { Toast, FormLabel, FormDropdown, FormRow },
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
      organization_city: null,
      organization_state: null,
      organization_zip: '',
      country: null,
      countries: [],
      states: [],
      cities: [],
      currentYear: new Date().getFullYear(),
      find_us: '',
      findUsOptions: [],
      showPassword: false,
      showConfirmPassword: false,
           loading: false,
      successMessage: '',
      errorMessage: '',
      errors: {},
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
          if (this.$refs.orgNameInput) this.focusRef('orgNameInput');
        }, 50);
      });
    },
    goToStep1() {
      this.step = 1;
      this.$nextTick(() => {
        setTimeout(() => {
          if (this.$refs.firstNameInput) this.focusRef('firstNameInput');
        }, 50);
      });
    },
    focusRef(refName, opts = { scroll: true }) {
      const ref = this.$refs[refName];
      if (!ref) return;
      const doFocus = (el) => {
        if (!el) return false;
        try {
          if (opts.scroll && el.scrollIntoView) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
          if (typeof el.focus === 'function') {
            el.focus();
            return true;
          }
        } catch (e) {
          console.warn('Error focusing element', e);
        }
        return false;
      };
      // Direct element or component with focus
      if (doFocus(ref)) return;
      // Component root or raw element
      const el = ref.$el || ref;
      if (!el) return;
      // Try common focusables inside component
      const candidate =
        (el.querySelector &&
          el.querySelector(
            'input, select, textarea, button, [tabindex]:not([tabindex="-1"])'
          )) ||
        null;
      if (doFocus(candidate)) return;
      // Fallback to root
      doFocus(el);
    },
    normalizeValidationErrors(errs) {
      if (!errs || typeof errs !== 'object') return {};
      const out = { ...errs };
      // Map backend keys to UI field keys
      if (errs.organization_name) out.organization_name = errs.organization_name;
      if (errs.organization_size) out.organization_size = errs.organization_size;
      if (errs.address) out.organization_address = errs.address;
      if (errs.zip) out.organization_zip = errs.zip;
      if (errs.state && !out.organization_state)
        out.organization_state = errs.state;
      if (errs.city && !out.organization_city)
        out.organization_city = errs.city;
      return out;
    },
  navigateToFirstError(errors) {
      const step1 = ['first_name', 'last_name', 'email', 'phone'];
      const step2 = [
        'organization_name',
        'organization_size',
        'find_us',
        'country',
        'state',
        'organization_state',
        'city',
        'organization_city',
        'organization_address',
        'organization_zip',
      ];
      const step3 = ['password', 'confirm_password'];
      const firstIn = (arr) => arr.find((k) => errors && errors[k]);
      let targetStep = 1;
      let targetRef = null;
      const s1 = firstIn(step1);
      const s2 = firstIn(step2);
      const s3 = firstIn(step3);
  if (s1) {
        const map = {
          first_name: 'firstNameInput',
          last_name: 'lastNameInput',
          email: 'emailInput',
          phone: 'phoneInput',
        };
        targetRef = map[s1] || 'firstNameInput';
      } else if (s2) {
        targetStep = 2;
        const map = {
          organization_name: 'orgNameInput',
          organization_size: 'orgSizeSelect',
          find_us: 'findUsSelect',
          country: 'countrySelect',
          state: 'stateSelect',
          organization_state: 'stateSelect',
          city: 'citySelect',
          organization_city: 'citySelect',
          organization_address: 'orgAddressInput',
          organization_zip: 'orgZipInput',
        };
        targetRef = map[s2] || 'countrySelect';
      } else if (s3) {
        targetStep = 3;
        const map = {
          password: 'passwordInput',
          confirm_password: 'confirmPasswordInput',
        };
        targetRef = map[s3] || 'passwordInput';
      }else {
        // No errors found
        return;
      }
      if (this.step !== targetStep) this.step = targetStep;
      this.$nextTick(() => {
        if (targetRef) this.focusRef(targetRef, { scroll: true });
      });
    },
    async handleRegister() {
      try {
        const response = await axios.post(
          `${API_BASE_URL}/api/register`,
          this.buildRegistrationPayload()
        );
        if (response.status === 201) {
          this.$router.push({
            name: 'Login',
            query: { email: this.email, registrationSuccess: true },
          });
        }
      } catch (error) {
        const msg = this.processRegistrationError(error);
        this.toast.add({
          severity: 'error',
          summary: 'Registration Error',
          detail: msg,
          life: 6000,
        });
      }
    },
    buildRegistrationPayload() {
      return {
        first_name: this.first_name,
        last_name: this.last_name,
        email: this.email,
        phone: this.phone,
        password: this.password,
        confirm_password: this.confirm_password,
        organization_name: this.organization_name,
        organization_size: this.organization_size,
        address: this.organization_address,
        city: this.organization_city,
        state: this.organization_state,
        zip: this.organization_zip,
        country: this.country,
        find_us: this.find_us,
      };
    },
    processRegistrationError(error) {
      console.error('Registration failed:', error);
      let errorMessage = 'Registration failed. Please try again.';
      const data = error?.response?.data;
      if (data) {
        console.error('Registration error response data:', data);
        const rawErrors = data.errors || (typeof data === 'object' ? data : null);
        const normalized = this.normalizeValidationErrors(rawErrors);
        if (normalized && Object.keys(normalized).length) {
          this.errors = normalized;
          this.navigateToFirstError(this.errors);
        }
        if (data.message) {
          errorMessage = data.message;
        } else if (data.errors) {
          errorMessage = Object.values(data.errors).flat().join(' ');
        } else if (typeof data === 'object') {
          const flat = Object.values(data).flat();
          if (flat.length) errorMessage = flat.join(' ');
        } else if (typeof data === 'string') errorMessage = data;
       
      } else if (error?.message) {
        errorMessage = error.message;
      }
      return errorMessage;
    },
    async prefillFromLead() {
      // Try to get lead data from query params or API
      const params = this.$route.query;
      let prefilled = this.setFromParams(params);
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
            this.setFromLead(res.data.lead);
            prefilled = true;
          }
        } catch (e) {
          console.warn('Prefill API failed, falling back to params', e);
        }
      }
      // Fallback: prefill from query params if backend prefill did not work
      if (!prefilled) this.setFromParams(params);
    },
    setFromParams(params = {}) {
      let changed = false;
      const set = (key, paramKey = key) => {
        if (params[paramKey] !== undefined) {
          this[key] = params[paramKey];
          changed = true;
        }
      };
      set('country');
      set('first_name');
      set('last_name');
      set('email');
      set('phone');
      set('organization_name');
      set('organization_size');
      set('organization_address');
      set('organization_city');
      set('organization_state');
      set('organization_zip');
      set('find_us');
      if (this.find_us) this.find_us = this.normalizeFindUs(this.find_us);
      if (this.organization_size)
        this.organization_size = this.normalizeOrgSize(this.organization_size);
      return changed;
    },
    setFromLead(lead = {}) {
      this.first_name = lead.first_name || '';
      this.last_name = lead.last_name || '';
      this.email = lead.email || '';
      this.phone = lead.phone || '';
      this.organization_name = lead.organization_name || '';
      this.organization_size = lead.organization_size || '';
      this.organization_address = lead.organization_address || '';
      this.country = lead.country_id || lead.country || this.country;
      this.organization_state =
        lead.organization_state_id ||
        lead.state_id ||
        lead.organization_state ||
        this.organization_state;
      this.organization_city =
        lead.organization_city_id ||
        lead.city_id ||
        lead.organization_city ||
        this.organization_city;
      this.organization_zip = lead.organization_zip || '';
      this.find_us = lead.find_us || '';
      if (this.find_us) this.find_us = this.normalizeFindUs(this.find_us);
      if (this.organization_size)
        this.organization_size = this.normalizeOrgSize(this.organization_size);
    },
    normalizeOrgSize(pref) {
 
      if (!pref && pref !== 0) return '';
      const v = String(pref).toLowerCase();
      if (v.includes('250') || v.includes('large') || /250\+/.test(v))
        return '250+ Employees (Large)';
      if (
        v.includes('100') ||
        v.includes('medium') ||
        /100-249/.test(v) ||
        /100\s*-\s*249/.test(v)
      )
        return '100-249 Employees (Medium)';
      if (
        v.includes('1') ||
        v.includes('small') ||
        /1-99/.test(v) ||
        /1\s*-\s*99/.test(v)
      )
        return '1-99 Employees (Small)';
      // If the incoming value already looks like one of the expected display
      // strings, return it unchanged.
      return String(pref);
    },
    normalizeFindUs(pref) {
      if (!pref && pref !== 0) return '';
      const v = String(pref).toLowerCase();
      if (v.includes('google')) return 'Google';
      if (v.includes('Colleague')) return 'Colleague';
      if (v.includes('friend') || v.includes('referral')) return 'Friend';
      if (v.includes('other')) return 'Other';
      // if it already matches one of the exact values, return as-is
      if (['Google', 'Friend', 'Colleague', 'Other'].includes(pref))
        return pref;
      return pref;
    },
    async fetchCountries() {
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      try {
        const res = await axios.get(`${API_BASE_URL}/api/countries`);
        this.countries = res.data || [];
        // If a country was prefilled (name or id), try to normalize it to id
        if (this.country) {
          const pref = this.country;
          let matched = null;
          // try numeric id
          const asNum = Number(pref);
          if (!isNaN(asNum) && asNum !== 0) {
            matched = this.countries.find((c) => Number(c.id) === asNum);
          }
          if (!matched) {
            // try matching by name or iso code (case-insensitive)
            const prefStr = String(pref).toLowerCase();
            matched = this.countries.find((c) => {
              return (
                (c.name && String(c.name).toLowerCase() === prefStr) ||
                (c.iso && String(c.iso).toLowerCase() === prefStr)
              );
            });
          }
          if (matched) {
            this.country = Number(matched.id);
            // after resolving country id, fetch states
            this.fetchStates();
          }
        }
      } catch (e) {
        console.warn('Failed to fetch countries', e);
      }
    },
    async fetchFindUsOptions() {
      try {
        const res = await axios.get(
          `${API_BASE_URL}/api/leads/find-us-options`
        );
        const opts = (res.data && res.data.options) || [];
        // normalize values to the dropdown option canonical forms where possible
        this.findUsOptions = opts
          .map((o) => {
            const v = String(o || '').trim();
            if (!v) return null;
            const low = v.toLowerCase();
            if (low.includes('google')) return 'Google';
            if (low.includes('Colleague')) return 'Colleague';
            if (low.includes('friend') || low.includes('referral'))
              return 'Friend';
            if (low.includes('other')) return 'Other';
            return v;
          })
          .filter(Boolean);
        if (this.findUsOptions.length === 0) {
          console.info('No find_us options found in leads');
        }
      } catch (e) {
        console.warn('Failed to fetch find_us options', e);
      }
    },
    async fetchStates() {
      if (!this.country) {
        this.states = [];
        return;
      }
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      const countryId = Number(this.country) || this.country;
      try {
        const res = await axios.get(`${API_BASE_URL}/api/states`, {
          params: { country_id: countryId },
        });
        this.states = res.data || [];
        // If a state was prefilled (name or id), normalize to id and fetch cities
        if (this.organization_state) {
          const pref = this.organization_state;
          let matched = null;
          const asNum = Number(pref);
          if (!isNaN(asNum) && asNum !== 0) {
            matched = this.states.find((s) => Number(s.id) === asNum);
          }
          if (!matched) {
            const prefStr = String(pref).toLowerCase();
            matched = this.states.find((s) => {
              return (
                (s.name && String(s.name).toLowerCase() === prefStr) ||
                (s.code && String(s.code).toLowerCase() === prefStr)
              );
            });
          }
          if (matched) {
            this.organization_state = Number(matched.id);
            this.fetchCities();
          }
        }
      } catch (e) {
        console.warn('Failed to fetch states', e);
        this.states = [];
      }
    },
    async fetchCities() {
      if (!this.organization_state) {
        this.cities = [];
        return;
      }
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      const stateId =
        Number(this.organization_state) || this.organization_state;
      try {
        const res = await axios.get(`${API_BASE_URL}/api/cities`, {
          params: { state_id: stateId },
        });
        this.cities = res.data || [];
        // Normalize prefilled city (name or id) to an id so dropdown selects it
        if (this.organization_city) {
          const pref = this.organization_city;
          let matched = null;
          const asNum = Number(pref);
          if (!isNaN(asNum) && asNum !== 0) {
            matched = this.cities.find((c) => Number(c.id) === asNum);
          }
          if (!matched) {
            const prefStr = String(pref).toLowerCase();
            matched = this.cities.find((c) => {
              return (
                (c.name && String(c.name).toLowerCase() === prefStr) ||
                (c.code && String(c.code).toLowerCase() === prefStr)
              );
            });
          }
          if (matched) {
            this.organization_city = Number(matched.id);
          }
        }
      } catch (e) {
        console.warn('Failed to fetch cities', e);
        this.cities = [];
      }
    },
    onCountryChange() {
      // normalize country to number when possible
      if (
        this.country !== null &&
        this.country !== '' &&
        typeof this.country !== 'number'
      ) {
        const n = Number(this.country);
        if (!isNaN(n)) this.country = n;
      }
      this.organization_state = null;
      this.organization_city = null;
      this.states = [];
      this.cities = [];
      this.fetchStates();
    },
    onStateChange() {
      if (
        this.organization_state !== null &&
        this.organization_state !== '' &&
        typeof this.organization_state !== 'number'
      ) {
        const n = Number(this.organization_state);
        if (!isNaN(n)) this.organization_state = n;
      }
      this.organization_city = null;
      this.cities = [];
      this.fetchCities();
    },
  },
  async mounted() {
    // Ensure lead prefill runs before fetching so we can resolve names -> ids
    await this.prefillFromLead();
    await this.fetchCountries();
    // fetch dependent lists if prefilled
    if (this.country) await this.fetchStates();
    if (this.organization_state) await this.fetchCities();
    // attempt to fetch existing find_us options from leads table
    await this.fetchFindUsOptions();
    this.$nextTick(() => {
      setTimeout(() => {
        if (this.$refs.firstNameInput) this.focusRef('firstNameInput');
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

/* Wider layout for Step 2 (Organization Info) */
.login-card:has(.org-form) {
  max-width: 780px;
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
    margin-bottom: 32px;
 
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
  margin: 16px;
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

/* Organization Form Layout Styles */
.org-form {
  text-align: left;
}

.form-row {
  display: flex;
  gap: 16px;

  width: 100%;
}

.form-row.full-width {
  flex-direction: column;
}

.form-row.two-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-row.three-columns {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
}

.form-field {
  flex: 1;
  min-width: 0;
}

.form-field .input-group {
  margin-bottom: 16px;
}

.address-field {
  flex: 2;
}

.zip-field {
  flex: 1;
}

.button-row {
  display: flex;
  gap: 12px;
  margin-top: 24px;
  justify-content: space-between;
}


.next-btn {
  flex: 1;
  margin-bottom: 16px !important;
  margin-top: 0 !important;
}

.back-btn {
    flex: 1;
  background: #6c757d;
}

.back-btn:hover {
  background: #5a6268;
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

  /* Wider layout for Step 2 on medium screens */
  .login-card:has(.org-form) {
    max-width: 700px;
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
    max-width: 95%;
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

  /* Mobile responsive for organization form */
  .form-row.two-columns,
  .form-row.three-columns {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .button-row {
    flex-direction: column;
    gap: 8px;
  }

  .back-btn,
  .next-btn {
    width: 100%;
  }
}

@media (max-width: 900px) {
  .form-row.three-columns {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .form-row.three-columns .form-field:last-child {
    grid-column: 1 / -1;
  }
}
@media (max-height: 900px) {
  .login-card {
    padding-top: 16px;
    padding-bottom: 16px;
    /* constrain card height and allow internal scrolling when vertical space is limited */
    max-height: calc(100vh - 32px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  .login-card::-webkit-scrollbar {
    width: 4px;
  }
  .login-card::-webkit-scrollbar-track {
    background: transparent;
  }
  .login-card::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.12);
    border-radius: 8px;
  }
}
.error-message1
 {
    color: red;
    font-size: 1em;
    margin-top: 8px;

}
</style>
