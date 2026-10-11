import { defineConfig } from 'vitepress'

const sidebar = [
  { text: 'Guide', items: [
    { text: 'Installation', link: '/guide/installation' },
    { text: 'Getting started', link: '/guide/getting-started' },
    { text: 'Messages and rendering', link: '/guide/messages' },
    { text: 'Transient messages', link: '/guide/transient' },
    { text: 'AJAX responses', link: '/guide/ajax' },
    { text: 'Templates', link: '/guide/templates' },
  ] },
  { text: 'Reference', items: [
    { text: 'Configuration', link: '/reference/configuration' },
    { text: 'Upgrading', link: '/reference/upgrading' },
  ] },
]

export default defineConfig({
  title: 'CakePHP Flash',
  description: 'Session, transient and AJAX flash messages for CakePHP.',
  base: '/cakephp-flash/',
  cleanUrls: true,
  lastUpdated: true,
  srcExclude: ['README.md'],
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-flash/' },
  head: [['link', { rel: 'icon', href: '/cakephp-flash/favicon.svg', type: 'image/svg+xml' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/configuration' },
    ],
    sidebar,
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-flash' }],
    search: { provider: 'local' },
    editLink: { pattern: 'https://github.com/dereuromark/cakephp-flash/edit/master/docs/:path' },
    footer: { message: 'Released under the MIT License.', copyright: 'Copyright Mark Scherer' },
  },
})
