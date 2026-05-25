<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 py-8 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Logo & Title -->
      <div class="text-center mb-8">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/20 mx-auto mb-4">
          <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20.57 14.86L22 13.43L20.57 12L17 15.57L8.43 7L12 3.43L10.57 2L9.14 3.43L7.71 2L5.57 4.14L4.14 2.71L2.71 4.14L4.14 5.57L2 7.71L3.43 9.14L2 10.57L3.43 12L7 8.43L15.57 17L12 20.57L13.43 22L14.86 20.57L16.29 22L18.43 19.86L19.86 21.29L21.29 19.86L19.86 18.43L22 16.29L20.57 14.86Z"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Hesap Oluştur</h1>
        <p class="text-slate-400">Fitness yolculuğuna adım at</p>
      </div>

      <!-- Progress Steps -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div v-for="(step, index) in steps" :key="index" class="flex items-center flex-1">
            <div class="flex flex-col items-center flex-1">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                :class="currentStep >= index ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white' : 'bg-slate-800 text-slate-500'"
              >
                <span v-if="currentStep > index">✓</span>
                <span v-else>{{ index + 1 }}</span>
              </div>
              <span class="text-xs mt-2 hidden sm:block" :class="currentStep >= index ? 'text-white' : 'text-slate-500'">{{ step }}</span>
            </div>
            <div v-if="index < steps.length - 1" class="flex-1 h-1 mx-2" :class="currentStep > index ? 'bg-gradient-to-r from-violet-500 to-purple-600' : 'bg-slate-800'"></div>
          </div>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-slate-900/50 backdrop-blur-xl rounded-3xl border border-slate-800/50 p-8 shadow-2xl">
        <!-- novalidate: gizli adımlardaki required alanlar "not focusable" hatası vermesin; doğrulama canGoNext ile -->
        <form @submit.prevent="onFormSubmit" autocomplete="on" novalidate>
        <!-- Step 1: Account Info (v-show: alanlar DOM'da kalır) -->
        <div v-show="currentStep === 0" class="space-y-5">
          <h2 class="text-xl font-bold text-white mb-6">Hesap Bilgileri</h2>

          <div>
            <label for="register-name" class="block text-sm font-medium text-slate-300 mb-2">Ad Soyad</label>
            <input
              id="register-name"
              v-model="form.name"
              name="name"
              type="text"
              autocomplete="name"
              placeholder="Ad Soyad"
              class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
            />
          </div>

          <div>
            <label for="register-email" class="block text-sm font-medium text-slate-300 mb-2">E-posta</label>
            <input
              id="register-email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              placeholder="ornek@email.com"
              class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
            />
          </div>

          <div>
            <label for="register-password" class="block text-sm font-medium text-slate-300 mb-2">Şifre</label>
            <div class="relative">
              <input
                id="register-password"
                v-model="form.password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="En az 8 karakter"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all pr-12"
              />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-300">
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <div class="mt-2 flex gap-1">
              <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="passwordStrengthColor(0)"></div>
              <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="passwordStrengthColor(1)"></div>
              <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="passwordStrengthColor(2)"></div>
              <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="passwordStrengthColor(3)"></div>
            </div>
            <p class="text-xs mt-1" :class="passwordStrengthTextColor">{{ passwordStrengthText }}</p>
          </div>

          <div>
            <label for="register-password-confirm" class="block text-sm font-medium text-slate-300 mb-2">Şifre Tekrar</label>
            <input
              id="register-password-confirm"
              v-model="form.password_confirmation"
              name="password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              autocomplete="new-password"
              placeholder="Şifreni tekrar gir"
              class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
              :class="{'border-red-500/50': form.password_confirmation && form.password !== form.password_confirmation}"
            />
            <p v-if="form.password_confirmation && form.password !== form.password_confirmation" class="text-red-400 text-xs mt-1">Şifreler eşleşmiyor</p>
          </div>
        </div>

        <!-- Step 2: Personal Info -->
        <div v-show="currentStep === 1" class="space-y-5">
          <h2 class="text-xl font-bold text-white mb-6">Kişisel Bilgiler</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="register-phone" class="block text-sm font-medium text-slate-300 mb-2">Telefon <span class="text-red-400">*</span></label>
              <input
                id="register-phone"
                v-model="form.phone"
                name="phone"
                type="tel"
                autocomplete="tel"
                placeholder="5XXXXXXXXX"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
              />
            </div>

            <div>
              <label for="register-gender" class="block text-sm font-medium text-slate-300 mb-2">Cinsiyet <span class="text-red-400">*</span></label>
              <select
                id="register-gender"
                v-model="form.gender"
                name="gender"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
              >
                <option value="">Seçiniz</option>
                <option value="male">Erkek</option>
                <option value="female">Kadın</option>
              </select>
            </div>

            <div>
              <label for="register-birth-date" class="block text-sm font-medium text-slate-300 mb-2">Doğum Tarihi <span class="text-red-400">*</span></label>
              <input
                id="register-birth-date"
                v-model="form.birth_date"
                name="birth_date"
                type="date"
                autocomplete="bday"
                :max="maxDate"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
              />
            </div>

            <div>
              <label for="register-height" class="block text-sm font-medium text-slate-300 mb-2">Boy (cm) <span class="text-red-400">*</span></label>
              <input
                id="register-height"
                v-model="form.height"
                name="height"
                type="number"
                placeholder="175"
                min="100"
                max="250"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
              />
            </div>

            <div>
              <label for="register-weight" class="block text-sm font-medium text-slate-300 mb-2">Kilo (kg) <span class="text-red-400">*</span></label>
              <input
                id="register-weight"
                v-model="form.weight"
                name="weight"
                type="number"
                placeholder="70"
                min="30"
                max="200"
                step="0.1"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
              />
            </div>

            <div>
              <label for="register-fitness-goal" class="block text-sm font-medium text-slate-300 mb-2">Fitness Hedefi</label>
              <select
                id="register-fitness-goal"
                v-model="form.fitness_goal"
                name="fitness_goal"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition-all"
              >
                <option value="">Seçiniz</option>
                <option value="weight_loss">Kilo Vermek</option>
                <option value="muscle_gain">Kas Kütlesi Yapmak</option>
                <option value="stay_fit">Formunu Korumak</option>
                <option value="strength">Güçlenmek</option>
                <option value="flexibility">Esneklik Kazanmak</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Step 3: Branch Selection -->
        <div v-show="currentStep === 2">
          <h2 class="text-xl font-bold text-white mb-6">Ne Üzerinde Çalışmak İstiyorsun?</h2>
          <p class="text-slate-400 mb-6">İlgilendiğin alanı seç, sana uygun programlar hazırlayalım.</p>

          <div v-if="loadingBranches" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-violet-500"></div>
          </div>

          <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
              v-for="branch in activeBranches"
              :key="branch.id"
              @click="selectBranch(branch.id)"
              class="p-6 rounded-2xl border-2 cursor-pointer transition-all hover:scale-105"
              :class="selectedBranch === branch.id ? 'border-violet-500 bg-violet-500/10' : 'border-slate-700 bg-slate-800/30 hover:border-slate-600'"
            >
              <div class="w-16 h-16 rounded-xl mx-auto mb-3 flex items-center justify-center overflow-hidden bg-slate-800 border border-slate-700">
                <img v-if="branch.image" :src="`http://localhost:8000/${branch.image}`" class="w-full h-full object-cover">
                <svg v-else class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
              </div>
              <p class="text-white text-sm font-medium text-center">{{ branch.name }}</p>
              <div v-if="selectedBranch === branch.id" class="mt-2 flex justify-center">
                <svg class="w-5 h-5 text-violet-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Membership Plans -->
        <div v-show="currentStep === 3">
          <h2 class="text-xl font-bold text-white mb-6">Üyelik Paketi Seç</h2>
          <p class="text-slate-400 mb-6">Sana en uygun paketi seç, şubemize gelerek ödeme yapabilirsin</p>

          <div v-if="loadingPlans" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-violet-500"></div>
          </div>

          <div v-else class="grid md:grid-cols-3 gap-4">
            <div
              v-for="plan in plans"
              :key="plan.id"
              @click="selectedPlan = plan.id"
              class="p-6 rounded-2xl border-2 cursor-pointer transition-all"
              :class="selectedPlan === plan.id ? 'border-violet-500 bg-violet-500/10' : 'border-slate-700 bg-slate-800/30 hover:border-slate-600'"
            >
              <div v-if="plan.is_featured" class="text-center mb-2">
                <span class="text-xs font-semibold text-violet-400">ÖNERİLEN</span>
              </div>
              <h3 class="text-lg font-bold text-white text-center">{{ plan.name }}</h3>
              <div class="text-center my-4">
                <span class="text-3xl font-bold text-violet-400">{{ parseFloat(plan.price).toFixed(0) }}</span>
                <span class="text-slate-400">₺</span>
              </div>
              <ul class="space-y-2 text-sm">
                <li class="flex items-center gap-2 text-slate-300">
                  <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  {{ plan.duration_days }} gün geçerli
                </li>
                <li v-if="plan.session_count" class="flex items-center gap-2 text-slate-300">
                  <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  {{ plan.session_count }} seans
                </li>
                <li v-if="plan.type === 'unlimited'" class="flex items-center gap-2 text-slate-300">
                  <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  Sınırsız kullanım
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mt-6 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
          {{ error }}
        </div>

        <!-- Seçimler (form state) -->
        <input type="hidden" name="selected_branch" :value="selectedBranch ?? ''">
        <input type="hidden" name="selected_plan" :value="selectedPlan ?? ''">

        <!-- Navigation Buttons -->
        <div class="mt-8 flex gap-4">
          <button
            v-if="currentStep > 0"
            type="button"
            @click="prevStep"
            class="flex-1 py-3 px-6 rounded-xl bg-slate-800 text-white font-medium hover:bg-slate-700 transition-all"
          >
            ← Geri
          </button>
          <button
            v-if="currentStep < steps.length - 1"
            type="button"
            :disabled="!canGoNext"
            @click="nextStep"
            class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-medium hover:shadow-lg hover:shadow-violet-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            Devam →
          </button>
          <button
            v-if="currentStep === steps.length - 1"
            type="submit"
            :disabled="loading || submitting || !canGoNext"
            class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-medium hover:shadow-lg hover:shadow-violet-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2"
          >
            <svg v-if="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Kayıt yapılıyor...' : 'Kayıt Ol' }}</span>
          </button>
        </div>
        </form>
      </div>

      <!-- Login Link -->
      <p class="text-center text-slate-400 mt-6">
        Zaten hesabın var mı?
        <NuxtLink to="/login" class="text-violet-400 hover:text-violet-300 font-medium transition-colors">
          Giriş Yap
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  middleware: 'guest',
})

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const toast = useToast()
const api = useApi()

const steps = ['Hesap', 'Kişisel', 'Branş', 'Paket']
const currentStep = ref(0)
const loading = ref(false)
const submitting = ref(false)
const error = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loadingBranches = ref(true)
const loadingPlans = ref(true)

const branches = ref<any[]>([])
const plans = ref<any[]>([])
const selectedBranch = ref<number | null>(null)
const selectedPlan = ref<number | null>(null)

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
  gender: '',
  birth_date: '',
  height: '',
  weight: '',
  fitness_goal: '',
})

const maxDate = computed(() => {
  const date = new Date()
  date.setFullYear(date.getFullYear() - 16)
  return date.toISOString().split('T')[0]
})

const activeBranches = computed(() => branches.value.filter(b => b.is_active))

// Password strength
const passwordStrength = computed(() => {
  const password = form.value.password
  if (!password) return 0
  let strength = 0
  if (password.length >= 8) strength++
  if (password.length >= 12) strength++
  if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++
  if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) strength++
  return strength
})

const passwordStrengthText = computed(() => {
  const strength = passwordStrength.value
  if (strength === 0) return ''
  if (strength === 1) return 'Zayıf'
  if (strength === 2) return 'Orta'
  if (strength === 3) return 'Güçlü'
  return 'Çok güçlü'
})

const passwordStrengthTextColor = computed(() => {
  const strength = passwordStrength.value
  if (strength <= 1) return 'text-red-400'
  if (strength === 2) return 'text-yellow-400'
  if (strength === 3) return 'text-green-400'
  return 'text-emerald-400'
})

const passwordStrengthColor = (index: number) => {
  const strength = passwordStrength.value
  if (index >= strength) return 'bg-slate-700'
  if (strength === 1) return 'bg-red-500'
  if (strength === 2) return 'bg-yellow-500'
  if (strength === 3) return 'bg-green-500'
  return 'bg-emerald-500'
}

const canGoNext = computed(() => {
  switch (currentStep.value) {
    case 0:
      return form.value.name && form.value.email && form.value.password &&
             form.value.password_confirmation && form.value.password === form.value.password_confirmation &&
             passwordStrength.value >= 2
    case 1:
      return form.value.phone && form.value.gender && form.value.birth_date &&
             form.value.height && form.value.weight
    case 2:
      return selectedBranch.value !== null
    case 3:
      return selectedPlan.value !== null
    default:
      return false
  }
})

const selectBranch = (branchId: number) => {
  selectedBranch.value = branchId
}

const isEmailTakenError = (e: any): boolean => {
  const emailErrors = e?.response?.data?.errors?.email
  if (!emailErrors) return false
  const messages = Array.isArray(emailErrors) ? emailErrors : [emailErrors]
  return messages.some((msg: string) =>
    msg.includes('already been taken')
    || msg.includes('zaten kayıtlı')
    || msg.includes('zaten kullanımda')
  )
}

const fieldLabels: Record<string, string> = {
  name: 'Ad soyad',
  email: 'E-posta',
  password: 'Şifre',
  phone: 'Telefon',
  gender: 'Cinsiyet',
  birth_date: 'Doğum tarihi',
  height: 'Boy',
  weight: 'Kilo',
  membership_plan_id: 'Üyelik paketi',
  preferred_branches: 'Branş',
}

const parseApiError = (e: any): string => {
  const data = e?.response?.data

  if (data?.errors) {
    return Object.entries(data.errors)
      .map(([field, msgs]) => {
        const label = fieldLabels[field] ?? field
        const text = Array.isArray(msgs) ? msgs.join(', ') : String(msgs)
        return `${label}: ${text}`
      })
      .join(' — ')
  }

  if (data?.error?.message) return data.error.message
  if (data?.message && data.message !== 'Doğrulama hatası') return data.message
  if (data?.message) return data.message
  if (e?.message) return e.message
  return 'Sunucu hatası oluştu. API çalışıyor mu kontrol edin.'
}

const normalizedEmail = () => form.value.email.trim().toLowerCase()

const clearAuthSession = () => {
  localStorage.removeItem('token')
  authStore.$patch({ token: null, user: null })
}

const checkEmailAvailable = async (): Promise<boolean> => {
  const email = encodeURIComponent(normalizedEmail())
  const res = await api.get(`/auth/check-email?email=${email}`)
  return res.data.available === true
}

const onFormSubmit = async () => {
  if (!canGoNext.value) return

  if (currentStep.value === steps.length - 1) {
    await handleRegister()
    return
  }
  await nextStep()
}

const nextStep = async () => {
  if (!canGoNext.value || currentStep.value >= steps.length - 1) return

  if (currentStep.value === 0) {
    error.value = ''
    try {
      const available = await checkEmailAvailable()
      if (!available) {
        error.value = 'Bu e-posta adresi zaten kayıtlı. Giriş yapın veya farklı bir e-posta kullanın.'
        return
      }
    } catch {
      error.value = 'E-posta kontrol edilemedi. Lütfen tekrar deneyin.'
      return
    }
  }

  error.value = ''
  currentStep.value++
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const fetchBranches = async () => {
  try {
    const res = await api.get('/branches')
    branches.value = res.data.data
  } catch (e) {
    console.error('Branşlar yüklenemedi', e)
  } finally {
    loadingBranches.value = false
  }
}

const fetchPlans = async () => {
  try {
    const res = await api.get('/membership-plans')
    plans.value = res.data.data.filter((p: any) => p.is_active)
  } catch (e) {
    console.error('Paketler yüklenemedi', e)
  } finally {
    loadingPlans.value = false
  }
}

const handleRegister = async () => {
  if (!canGoNext.value || submitting.value) return

  error.value = ''
  submitting.value = true
  loading.value = true

  clearAuthSession()

  try {
    const email = normalizedEmail()
    const response = await api.post('/auth/register-complete', {
      name: form.value.name.trim(),
      email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      phone: form.value.phone,
      birth_date: form.value.birth_date,
      gender: form.value.gender,
      height: parseFloat(form.value.height),
      weight: parseFloat(form.value.weight),
      fitness_goal: form.value.fitness_goal || null,
      preferred_branches: selectedBranch.value ? [selectedBranch.value] : [],
      membership_plan_id: selectedPlan.value,
    })

    authStore.$patch({
      token: response.data.token,
      user: response.data.user,
    })
    localStorage.setItem('token', response.data.token)

    toast.success('🎉 Kayıt başarılı! Üyelik başvurunuz alındı. Şubemize gelerek ödeme yapabilirsiniz.')
    await router.push('/dashboard')
  } catch (e: any) {
    console.error('register-complete error:', e?.response?.data ?? e)

    let message = parseApiError(e)

    if (isEmailTakenError(e)) {
      message = 'Bu e-posta adresi zaten kayıtlı. Giriş yapın veya farklı bir e-posta deneyin.'
    }

    error.value = message
    toast.error(message)
  } finally {
    loading.value = false
    submitting.value = false
  }
}

onMounted(() => {
  clearAuthSession()
  fetchBranches()
  fetchPlans()
})
</script>
