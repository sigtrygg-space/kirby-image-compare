// Minify src/frontend/* -> assets/* (the paths index.php references).
// Banner-preserving: /*! */ stays, every other comment is stripped.
// --check builds into memory and diffs against the committed output
// (CI / release guard), exiting non-zero on drift.
import { build } from 'esbuild'
import { readFile, writeFile } from 'node:fs/promises'
import { fileURLToPath } from 'node:url'
import { dirname, join } from 'node:path'

const root  = join(dirname(fileURLToPath(import.meta.url)), '..')
const check = process.argv.includes('--check')

const targets = [
	{ in: 'src/frontend/image-compare.js',  out: 'assets/image-compare.js'  },
	{ in: 'src/frontend/image-compare.css', out: 'assets/image-compare.css' },
]

let drift = false
for (const t of targets) {
	const res = await build({
		entryPoints: [join(root, t.in)],
		minify: true,
		legalComments: 'inline',   // keep /*! */, strip the rest
		write: false,
	})
	const built = res.outputFiles[0].text
	if (check) {
		const current = await readFile(join(root, t.out), 'utf8').catch(() => '')
		if (current !== built) { drift = true; console.error(`drift: ${t.out} is stale — run "npm run build"`) }
	} else {
		await writeFile(join(root, t.out), built)
		console.log(`built ${t.out} (${built.length} B)`)
	}
}
if (check && drift) process.exit(1)
