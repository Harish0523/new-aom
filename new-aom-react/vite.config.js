import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs'
import { join, extname } from 'node:path'
import postcss from 'postcss'

// Icons can be picked from the CMS at runtime, and Bootstrap's JS toggles these state classes.
const KEEP_CLASS = [
  /^bi(-|$)/,
  /^(show|active|collapsed|collapsing|fade|modal-open|modal-backdrop|was-validated|is-invalid|is-valid|invalid-feedback|valid-feedback)$/,
]

function escapeClass(cls) {
  return cls.replace(/[.*+?^${}()|[\]\\-]/g, '\\$&')
}

// Drops CSS rules whose classes never appear in the built HTML/JS (e.g. unused Bootstrap components).
function purgeCssPlugin() {
  return {
    name: 'aom-purge-css',
    apply: 'build',
    closeBundle() {
      const outDir = join(process.cwd(), 'dist')
      const files = []
      const walk = (dir) => {
        for (const entry of readdirSync(dir)) {
          const path = join(dir, entry)
          if (statSync(path).isDirectory()) walk(path)
          else files.push(path)
        }
      }
      walk(outDir)

      const corpus = files
        .filter((f) => ['.js', '.html', '.json'].includes(extname(f)))
        .map((f) => readFileSync(f, 'utf8'))
        .join('\n')

      const cache = new Map()
      const used = (cls) => {
        if (cache.has(cls)) return cache.get(cls)
        const found = KEEP_CLASS.some((re) => re.test(cls))
          || new RegExp(`(^|[^\\w-])${escapeClass(cls)}([^\\w-]|$)`).test(corpus)
        cache.set(cls, found)
        return found
      }

      for (const file of files.filter((f) => f.endsWith('.css'))) {
        const root = postcss.parse(readFileSync(file, 'utf8'))
        root.walkRules((rule) => {
          if (!rule.selectors) return
          const keptSelectors = rule.selectors.filter((selector) => {
            const classes = selector.match(/\.[A-Za-z_-][\w-]*/g) || []
            return classes.every((token) => used(token.slice(1)))
          })
          if (keptSelectors.length === 0) rule.remove()
          else rule.selectors = keptSelectors
        })
        root.walkAtRules('media', (atRule) => {
          if (!atRule.nodes || atRule.nodes.length === 0) atRule.remove()
        })
        writeFileSync(file, root.toString())
      }
    },
  }
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), purgeCssPlugin()],
})
