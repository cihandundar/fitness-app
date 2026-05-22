<template>
  <div :class="wrapperClasses">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium mb-2"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="text-red-400 ml-1">*</span>
    </label>

    <!-- Input Wrapper -->
    <div class="relative">
      <!-- Prefix Icon -->
      <div
        v-if="prefixIcon"
        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"
      >
        <component :is="prefixIcon" class="w-5 h-5" />
      </div>

      <!-- Input -->
      <input
        :id="inputId"
        ref="inputRef"
        v-model="modelValue"
        :type="computedType"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :minlength="minlength"
        :maxlength="maxlength"
        :min="min"
        :max="max"
        :step="step"
        :class="inputClasses"
        @focus="handleFocus"
        @blur="handleBlur"
      >

      <!-- Suffix Icon (Password Toggle, Clear, etc.) -->
      <div
        v-if="suffixIcon || canTogglePassword || canClear"
        class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1"
      >
        <!-- Clear Button -->
        <button
          v-if="canClear && !disabled && !readonly"
          type="button"
          @click="handleClear"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-300 hover:bg-slate-700/50 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Password Toggle -->
        <button
          v-if="canTogglePassword"
          type="button"
          @click="togglePassword"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-300 hover:bg-slate-700/50 transition-colors"
        >
          <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
          </svg>
        </button>

        <!-- Custom Suffix Icon -->
        <component
          v-if="suffixIcon"
          :is="suffixIcon"
          class="w-5 h-5 text-slate-400 pointer-events-none"
        />
      </div>
    </div>

    <!-- Helper Text / Error Message -->
    <p v-if="helperText || error" class="mt-1.5 text-xs" :class="helperTextClasses">
      {{ error || helperText }}
    </p>

    <!-- Character Count -->
    <p v-if="maxlength && showCharCount" class="mt-1 text-xs text-slate-500 text-right">
      {{ String(modelValue || '').length }} / {{ maxlength }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, nextTick } from 'vue'

export interface InputProps {
  modelValue: string | number
  type?: string
  label?: string
  placeholder?: string
  helperText?: string
  error?: string
  disabled?: boolean
  readonly?: boolean
  required?: boolean
  minlength?: number
  maxlength?: number
  min?: number | string
  max?: number | string
  step?: number | string
  prefixIcon?: any
  suffixIcon?: any
  showCharCount?: boolean
  canClear?: boolean
}

const props = withDefaults(defineProps<InputProps>(), {
  type: 'text',
  disabled: false,
  readonly: false,
  required: false,
  showCharCount: false,
  canClear: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
  focus: [event: FocusEvent]
  blur: [event: FocusEvent]
}>()

const inputId = `input-${Math.random().toString(36).slice(2, 9)}`
const inputRef = ref<HTMLInputElement>()
const showPassword = ref(false)
const isFocused = ref(false)

const modelValue = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})

const computedType = computed(() => {
  if (props.type === 'password') {
    return showPassword.value ? 'text' : 'password'
  }
  return props.type
})

const canTogglePassword = computed(() => props.type === 'password')

const wrapperClasses = computed(() => {
  return props.disabled ? 'opacity-50' : ''
})

const labelClasses = computed(() => {
  if (props.error) return 'text-red-400'
  return 'text-slate-300'
})

const inputClasses = computed(() => {
  const classes = [
    'w-full px-4 py-3 rounded-xl bg-slate-800/50 border transition-all duration-200',
    'text-white placeholder-slate-500',
    'focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50',
  ]

  // Padding for icons
  if (props.prefixIcon) {
    classes.push('pl-11')
  }
  if (props.suffixIcon || canTogglePassword.value || props.canClear) {
    classes.push('pr-11')
  }

  // States
  if (props.error) {
    classes.push('border-red-500/50 focus:ring-red-500/50 focus:border-red-500/50')
  } else if (isFocused.value) {
    classes.push('border-violet-500/50')
  } else {
    classes.push('border-slate-700/50')
  }

  // Disabled
  if (props.disabled) {
    classes.push('cursor-not-allowed opacity-50')
  }

  // Readonly
  if (props.readonly) {
    classes.push('cursor-default')
  }

  return classes.join(' ')
})

const helperTextClasses = computed(() => {
  if (props.error) return 'text-red-400'
  return 'text-slate-500'
})

const handleFocus = (e: FocusEvent) => {
  isFocused.value = true
  emit('focus', e)
}

const handleBlur = (e: FocusEvent) => {
  isFocused.value = false
  emit('blur', e)
}

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const handleClear = () => {
  modelValue.value = ''
  inputRef.value?.focus()
}

const focus = () => {
  inputRef.value?.focus()
}

defineExpose({
  focus,
})
</script>
