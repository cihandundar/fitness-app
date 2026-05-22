<template>
  <span :class="badgeClasses" v-bind="$attrs">
    <slot />
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

export interface BadgeProps {
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger' | 'info'
  size?: 'xs' | 'sm' | 'md'
  dot?: boolean
  pill?: boolean
}

const props = withDefaults(defineProps<BadgeProps>(), {
  variant: 'default',
  size: 'sm',
  dot: false,
  pill: false,
})

const badgeClasses = computed(() => {
  const classes = ['inline-flex items-center justify-center font-medium']

  // Variant
  switch (props.variant) {
    case 'default':
      classes.push('bg-slate-800 text-slate-300')
      break
    case 'primary':
      classes.push('bg-violet-500/20 text-violet-400 border border-violet-500/30')
      break
    case 'success':
      classes.push('bg-emerald-500/20 text-emerald-400 border border-emerald-500/30')
      break
    case 'warning':
      classes.push('bg-amber-500/20 text-amber-400 border border-amber-500/30')
      break
    case 'danger':
      classes.push('bg-red-500/20 text-red-400 border border-red-500/30')
      break
    case 'info':
      classes.push('bg-blue-500/20 text-blue-400 border border-blue-500/30')
      break
  }

  // Size
  switch (props.size) {
    case 'xs':
      classes.push('px-2 py-0.5 text-[10px]')
      break
    case 'sm':
      classes.push('px-2 py-1 text-xs')
      break
    case 'md':
      classes.push('px-2.5 py-1 text-sm')
      break
  }

  // Shape
  if (props.pill) {
    classes.push('rounded-full')
  } else {
    classes.push('rounded-md')
  }

  // Dot variant
  if (props.dot) {
    classes.push('gap-1.5')
  }

  return classes.join(' ')
})
</script>
