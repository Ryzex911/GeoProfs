# GeoProfs Verlofregistratiesysteem

Dit project is een **verlofregistratiesysteem** ontwikkeld voor **GeoProfs**.  
Het systeem digitaliseert de registratie van verlof en ziekte en vervangt de huidige Excel-aanpak.  

## Functionaliteiten
### Voor medewerkers
- Online verlof aanvragen (via web of mobiel)
- Verlofsaldo inzien
- Afdelingsplanning bekijken
- Status van aanvragen volgen

### Voor managers
- Verlofaanvragen goedkeuren of afwijzen
- Realtime inzicht in bezetting en afwezigheid
- Melding bij onverwachte afwezigheid
- Planningen aanpassen

## Doel
Een gebruiksvriendelijke en efficiënte webapplicatie die het plannen en registreren van verlof bij GeoProfs eenvoudiger en betrouwbaarder maakt.

## Technische stack
- **Frontend**: React  
- **Backend**: Laravel  
- **Database**: MySQL / PostgreSQL  
- **Tools**: Git, Docker, CI/CD

## Branches
- **Base branches:** main (release)
- Develop (active development)
- Always create branches from develop
- **Naming:** feature/US001-topic, bugfix/US034-…, chore/….

## Pull Requests
- **Always target develop** (never commit directly to main)
- **Title:** US001: short title
- **Description:** what & why + link to Trello card
- At least 1 review required; all checks must pass

## Commits
- Written in English. Types: feat, fix, chore, refactor
- **Format:**
  feat(leave-requests): add approval endpoint (US001)
  Body (optional): details on what changed and impact
- End of workday: commit status update

## Push Rules 
- Always push your branch, even if the task is not completed

## Trello
- Board: [https://trello.com/invite/b/68bacad155e924dade9bad2e/ATTIdad1476bb6b7327025d40b521575cd7f8DFD4E8A/geoprofs]
- PR description must include the corresponding Trello card link

## Do Not:
- Commit directly to main or develop
- Merge without review or with failing checks

## Team & verantwoordelijkheden
- **Ryzex** – Web (frontend)  
- **Tamzid** – Web & Software  
- **Osama** – Backend & Software  
- **Lukas** – Backend & Software  

## Installatie
1. Clone de repository  
   ```bash
   git clone https://github.com/Ryzex911/GeoProfs.git
