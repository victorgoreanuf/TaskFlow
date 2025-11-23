<template>
  <div class="auth-wrapper auth-v2">
    <b-row class="auth-inner m-0">

      <!-- Brand logo-->
      <b-link class="brand-logo">
        <vuexy-logo/>
        <h2 class="brand-text text-primary ml-1">
          Vuexy
        </h2>
      </b-link>
      <!-- /Brand logo-->

      <!-- Left Text-->
      <b-col
          lg="8"
          class="d-none d-lg-flex align-items-center p-5"
      >
        <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
          <b-img
              fluid
              :src="imgUrl"
              alt="Login V2"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Login-->
      <b-col
          lg="4"
          class="d-flex align-items-center auth-bg px-2 p-lg-5"
      >
        <b-col
            sm="8"
            md="6"
            lg="12"
            class="px-xl-2 mx-auto"
        >
          <b-card-title
              title-tag="h2"
              class="font-weight-bold mb-1"
          >
            Welcome to Vuexy! 👋
          </b-card-title>
          <b-card-text class="mb-2">
            Please sign-up to your account and start the adventure
          </b-card-text>

          <!-- form -->
          <validation-observer ref="form">
            <b-form
                class="auth-login-form mt-2"
                @submit.prevent="submit"
            >
              <!-- username -->
              <b-form-group
                  label="Username"
                  label-for="register-username"
              >
                <validation-provider
                    #default="{ errors }"
                    name="username"
                    rules="required|min:6|max:20"
                >
                  <b-form-input
                      id="register-username"
                      v-model="form.username"
                      :state="errors.length > 0 ? false:null"
                      name="register-username"
                      placeholder="Oierjohn"
                      autocomplete="username"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- email -->
              <b-form-group
                  label="Email"
                  label-for="register-email"
              >
                <validation-provider
                    #default="{ errors }"
                    name="email"
                    rules="required|email"
                >
                  <b-form-input
                      id="register-email"
                      v-model="form.email"
                      :state="errors.length > 0 ? false:null"
                      name="register-email"
                      placeholder="john@example.com"
                      type="email"
                      autocomplete="email"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- password -->
              <b-form-group>
                <div class="d-flex justify-content-between">
                  <label for="register-password">Password</label>
                </div>
                <validation-provider
                    #default="{ errors }"
                    name="Password"
                    rules="required|min:8|regex:^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])"
                    vid="password"
                >
                  <b-input-group
                      class="input-group-merge"
                      :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                        id="register-password"
                        v-model="form.password"
                        :state="errors.length > 0 ? false:null"
                        class="form-control-merge"
                        :type="passwordFieldType"
                        name="register-password"
                        placeholder="············"
                        autocomplete="new-password"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                          class="cursor-pointer"
                          :icon="passwordToggleIcon"
                          @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- confirm password -->
              <b-form-group>
                <div class="d-flex justify-content-between">
                  <label for="register-password-confirm">Confirm Password</label>
                </div>
                <validation-provider
                    #default="{ errors }"
                    name="Password Confirmation"
                    rules="required|confirmed:password"
                >
                  <b-input-group
                      class="input-group-merge"
                      :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                        id="register-password-confirm"
                        v-model="form.passwordConfirm"
                        :state="errors.length > 0 ? false:null"
                        class="form-control-merge"
                        :type="passwordFieldType"
                        name="register-password-confirm"
                        placeholder="············"
                        autocomplete="new-password"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                          class="cursor-pointer"
                          :icon="passwordToggleIcon"
                          @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- checkbox -->
              <b-form-group>
                <validation-provider
                    #default="{ errors }"
                    name="Terms & Conditions"
                    rules="required"
                >
                  <b-form-checkbox
                      id="register-tca"
                      v-model="form.isTCAccepted"
                      name="checkbox-tca"
                      value="true"
                      unchecked-value="false"
                      :state="errors.length > 0 ? false:null"
                  >
                    I agree to the
                    <b-link to="/pages/terms-of-service" class="text-primary">Terms & Conditions</b-link>
                  </b-form-checkbox>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- submit buttons -->
              <b-button
                  :disabled="processing"
                  type="submit"
                  variant="primary"
                  block
              >
                <b-spinner v-if="processing" small type="grow"/>
                Register
              </b-button>
            </b-form>
          </validation-observer>
          <div class="mt-2 flex font-small-3 justify-content-center w-100 text-center ">
            <p>Already have an account? <b><a :href="link">Sign in</a></b></p>
          </div>
        </b-col>
      </b-col>
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import VuexyLogo from '@core/layouts/components/Logo.vue'
import {togglePasswordVisibility} from '@core/mixins/ui/forms'
// Import Validation components needed for the form
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, confirmed, min, regex } from '@core/utils/validations/validations' // Import required validation rules

export default {
  components: {
    VuexyLogo,
    ValidationProvider, // Add Validation components
    ValidationObserver, // Add Validation components
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      // 1. Link to Login after successful registration
      link: window.location.origin + "/login",

      // 2. Updated Form Data Structure for Registration Fields
      form: {
        username: null, // NEW: Username field
        email: null,
        password: null,
        passwordConfirm: null, // NEW: Confirm Password field
        isTCAccepted: 'false', // NEW: Terms and Conditions field (Use string 'false' for checkbox unchecked-value)
      },

      // 3. Validation Rules (for use in the template if needed, though they are inline)
      required,
      email,
      confirmed,
      min,
      regex,

      processing: false,
      // Change the side image to a registration or generic one if available, otherwise keep the default
      sideImg: require('@/assets/images/pages/register-v2.svg'), // Assuming 'register-v2.svg' exists
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    imgUrl() {
      if (this.$store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/register-v2-dark.svg') // Dark mode image change
        return this.sideImg
      }
      return this.sideImg
    },
  },
  methods: {
    // 4. Update the submit method for Registration
    submit() {
      this.processing = true;

      // Use the ref from the ValidationObserver (assuming you wrap your form with <validation-observer ref="form">)
      this.$refs.form.validate().then((success) => {
        if (success) {
          // 5. Change Vuex action from 'auth/login' to 'auth/register' or 'auth/signup'
          this.$store.dispatch('auth/register', this.form)
              .then(() => {
                // 6. Update Success Message and Redirect
                this.softToast('success', this.$t('Success'), this.$t("Successfully registered! You can now log in."), 'CheckIcon')
                this.$router.push({name: 'home'}); // Redirect to Login page after successful registration
              })
              .catch(error => this.handleResponseError(error, this.$refs.form))
              .finally(() => {
                this.processing = false
              });
        } else {
          this.processing = false
        }
      }).catch(() => {
        this.processing = false
      })
    },
  },
  mounted() {
    // 7. Update document title
    document.title = this.$t('Register');
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-auth.scss';
</style>