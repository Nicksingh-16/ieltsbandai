# General Training Listening — 8-test build plan

Status: 1 POC built (`gt_test_01.json`), 7 to go.

## Source mix (per IELTS GT Listening conventions)

| Section | Style | Source family | License |
|---|---|---|---|
| **S1** | 2-speaker transactional dialogue | **VOA Let's Learn English (Anna series)** L1+L2 | Public domain (US fed gov) |
| **S2** | Monologue, social/practical | **NPS Park Postcards** + VOA Words and Their Stories | Public domain |
| **S3** | Small-group discussion (vocational/training) | **NPS A Sense of Place** interviews + (TTS fallback for 2-3 tests) | Public domain |
| **S4** | Monologue, general-interest talk | **VOA Words and Their Stories** + LibriVox short nonfiction (curated) | Public domain |

## Per-test topic plan

| # | S1 (transactional) | S2 (local/services) | S3 (training/work) | S4 (general talk) |
|---|---|---|---|---|
| 01 (POC) | Apartment rental enquiry | National park tour | Workplace training feedback | Origin of an English idiom |
| 02 | Gym membership sign-up | Local museum opening | Volunteer programme briefing | Local history monologue |
| 03 | Restaurant booking + dietary needs | Public transport schedule announcement | Hospitality course module | How a craft is made |
| 04 | Library card registration | Community festival overview | Driving school class | Health & wellness tip |
| 05 | Doctor's appointment booking | Bus tour of historic district | Apprentice programme intro | Saving energy at home |
| 06 | Lost-property report at a station | Walking tour of a botanical garden | First-aid certification briefing | Language origin story |
| 07 | Course enrolment at adult education | Radio announcement of community fair | Café-staff onboarding | Practical home repair |
| 08 | Hotel reservation by phone | Town hall public notice | Office IT training | Famous folk tale (LibriVox) |

## Question writing protocol (per section, 10 questions)

- Questions 1-5: short-answer / sentence completion (max 3 words)
- Questions 6-10: multiple choice (4 options)
- Mix of factual recall + inference
- All answers must be VERBATIM in the audio (or directly inferable in 1 step)

## Build order

For each test:
1. Listen to / transcribe the 4 chosen audios (2-4 min each)
2. Trim each to 3-4 min if longer (use `ffmpeg -ss -t`)
3. Upload trimmed clips to a CDN OR hot-link from the source CDN if direct MP3 URL is available
4. Write 40 questions
5. Add JSON spec to `database/seeders/data/gt_test_NN.json`
6. Add path to `VoaListeningSeeder::$specs`

## Estimate

- Per test: ~45 min (listen + question writing)
- 7 tests remaining: ~5–6 hrs of focused work
- Realistic timeline: spread over 2–3 sessions
