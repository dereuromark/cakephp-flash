---
layout: home
hero:
  name: CakePHP Flash
  text: Messages for pages and AJAX responses
  tagline: Queue session messages, display messages in the current view, or send them to JavaScript in a response header.
  image:
    src: /logo.svg
    alt: CakePHP Flash speech bubble and lightning bolt
  actions:
    - theme: brand
      text: Installation
      link: /guide/installation
    - theme: alt
      text: Show your first message
      link: /guide/getting-started
features:
  - title: Session messages
    details: Keep messages across redirects and render them once in your layout.
  - title: Current request
    details: Add transient messages from a controller or view without writing to the session.
  - title: AJAX headers
    details: Read structured messages from X-Flash and display them with your own JavaScript.
---

Start with [installation](./guide/installation.md), then follow the [beginner guide](./guide/getting-started.md). The plugin replaces CakePHP's Flash component and helper; your existing layout render call still works.
