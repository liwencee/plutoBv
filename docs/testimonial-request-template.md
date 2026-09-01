# Collecting real testimonials

Three short templates for getting genuine quotes you can legally publish.
The key things to capture every time: the words are theirs, and they have
explicitly agreed to have their name shown on the website.

---

## Email 1 - Care provider / registered manager

Subject: Would you mind a quick word about working with us?

Hi [Name],

We're putting a new Plutobv website together, and I'd like to include a
short line from the providers we work with rather than the usual generic
marketing copy.

Would you be happy to write a sentence or two about how the staffing side
has gone from your end? Anything honest is useful - continuity of staff,
how we handled short-notice cover, how the paperwork stacked up.

If you're happy for it to go on the site, could you confirm the name and
job title you'd like shown? Something like "Jane Adeyemi, Registered
Manager, Oakfield House". If you'd rather it were anonymous, that's fine
too and we'll use your role only.

Thanks,
[Your name]
Plutobv - 07932 790842

---

## Email 2 - Family of a client

Subject: A quick favour, if you have a moment

Hi [Name],

I hope [client's first name] is doing well.

We're refreshing the Plutobv website and would like to include a few words
from families we've supported. If you felt the care went well, would you be
willing to write a couple of sentences about your experience?

No pressure at all, and no particular form it needs to take. If you are
happy for us to publish it, please let me know what name you'd like shown -
first name only is completely fine, and we can leave out any detail about
[client's first name] that you'd rather keep private.

Thanks,
[Your name]
Plutobv - 07932 790842

---

## Email 3 - A care worker you've placed

Subject: Would you write a line about working with us?

Hi [Name],

We're rebuilding the Plutobv website and want to give people a realistic
picture of what it's like to be placed through us rather than just the
sales pitch.

Would you write a sentence or two about your experience - the placement
process, how shifts have been, how we've been to deal with? Honest is more
useful than glowing.

If you're happy for it to appear on the site, let me know the name and role
you'd like shown.

Thanks,
[Your name]
Plutobv - 07932 790842

---

## Before publishing any of them

- Keep the written reply (email is fine) as your record of consent. If a
  regulator or the CMA ever asks, that message is the proof the review is
  genuine.
- Publish their words. Fixing a typo or trimming length is fine; rewriting
  the substance is not.
- Use the name and role they actually agreed to. If they asked to stay
  anonymous, use the role alone - "Registered manager, residential care
  home" is still a real testimonial.
- Do not add star ratings unless they gave you one.

## Where they go on the site

`index.html`, in the section marked `<!-- DRAFT COPY - NOT REAL REVIEWS -->`.
Replace each block's quote, `testimonial-card__author` (the name) and
`testimonial-card__role` (role and organisation), then delete that HTML
comment. If you only collect one or two, delete the spare cards - the grid
handles one, two or three cards.
