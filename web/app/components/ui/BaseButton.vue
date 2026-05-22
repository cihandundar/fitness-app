<template>
  <component
    :is="tag"
    :type="type"
    :to="to"
    :href="href"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <!-- Loading Spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <!-- Icon -->
    <component
      v-if="icon && !loading"
      :is="icon"
      :class="iconClasses"
    />

    <!-- Slot Content -->
    <slot />
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'

export interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger' | 'success'
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl'
  disabled?: boolean
  loading?: boolean
  block?: boolean
  icon?: any
  iconPosition?: 'left' | 'right'
  type?: 'button' | 'submit' | 'reset'
  to?: string
  href?: string
}

const props = withDefaults(defineProps<ButtonProps>(), {
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
  block: false,
  iconPosition: 'left',
  type: 'button',
})

const emit = defineEmits<{
  click: [event: Event]
}>()

const tag = computed(() => {
  if (props.to) return 'NuxtLink'
  if (props.href) return 'a'
  return 'button'
})

const buttonClasses = computed(() => {
  const classes = [
    'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950',
  ]

  // Variant
  if (props.disabled || props.loading) {
    classes.push('opacity-50 cursor-not-allowed')
  }

  switch (props.variant) {
    case 'primary':
      classes.push(
        'bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/20 hover:shadow-violet-500/40 focus:ring-violet-500'
      )
      break
    case 'secondary':
      classes.push(
        'bg-slate-800 text-white border border-slate-700 hover:bg-slate-700 focus:ring-slate-500'
      )
      break
    case 'ghost':
      classes.push(
        'bg-transparent text-slate-300 hover:bg-slate-800/50 hover:text-white focus:ring-slate-500'
      )
      break
    case 'danger':
      classes.push(
        'bg-red-500 text-white shadow-lg shadow-red-500/20 hover:bg-red-600 focus:ring-red-500'
      )
      break
    case 'success':
      classes.push(
        'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 focus:ring-emerald-500'
      )
      break
  }

  // Size
  switch (props.size) {
    case 'xs':
      classes.push('px-2.5 py-1 text-xs')
      break
    case 'sm':
      classes.push('px-3 py-1.5 text-sm')
      break
    case 'md':
      classes.push('px-4 py-2 text-sm')
      break
    case 'lg':
      classes.push('px-5 py-2.5 text-base')
      break
    case 'xl':
      classes.push('px-6 py-3 text-lg')
      break
  }

  // Block
  if (props.block) {
    classes.push('w-full')
  }

  return classes.join(' ')
})

const iconClasses = computed(() => {
  const classes = ['w-4 h-4']

  if (props.iconPosition === 'left') {
    classes.push('mr-2', '-ml-1')
  } else {
    classes.push('ml-2', '-mr-1')
  }

  return classes.join(' ')
})

const handleClick = (e: Event) => {
  if (!props.disabled && !props.loading) {
    emit('click', e)
  }
}
</script>
