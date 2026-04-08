/**
 * Converts a decimal hours value to a human-readable string.
 * Examples: 0.25 → "15min"  |  0.5 → "30min"  |  1.0 → "1h"  |  1.75 → "1h 45min"
 */
export function formatHoras(h) {
    if (h === null || h === undefined || h === '' || h === false) return '—'
    const val = parseFloat(h)
    if (isNaN(val) || val <= 0) return '—'
    const totalMins = Math.round(val * 60)
    const hours = Math.floor(totalMins / 60)
    const mins  = totalMins % 60
    if (hours === 0) return `${mins}min`
    if (mins  === 0) return `${hours}h`
    return `${hours}h ${mins}min`
}
