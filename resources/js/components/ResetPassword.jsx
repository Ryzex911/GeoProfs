import React from 'react'
import '../../css/reset-password.css'

export default function ResetPassword() {
    return (
        <div className="page page--auth">
            <aside className="visual">
                <div className="visual__overlay" />
                <div className="visual__brand">
                    <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true" className="logo">
                        <path d="M3 12l6-6 6 6-6 6-6-6z" fill="none" stroke="currentColor" strokeWidth="1.5"/>
                        <path d="M9 6l6 6" stroke="currentColor" strokeWidth="1.5" fill="none" />
                    </svg>
                    <span>GeoProfs</span>
                </div>
            </aside>

            <main className="card">
                <header className="card__header">
                    <h1>Wachtwoord resetten</h1>
                    <p className="muted">Vul je e-mail in. We sturen je een resetlink.</p>
                </header>

                <form className="form" onSubmit={(e)=>e.preventDefault()}>
                    <div className="field">
                        <label htmlFor="email">E-mailadres</label>
                        <input id="email" name="email" type="email" placeholder="naam@bedrijf.nl" />
                        <small className="hint">Je ontvangt binnen enkele minuten een e-mail.</small>
                    </div>

                    <button className="btn btn--primary" type="button">
                        <svg width="18" height="18" viewBox="0 0 24 24" className="btn__icon" aria-hidden="true">
                            <path d="M21 12a9 9 0 10-3.51 7.11M21 12l-3-3m3 3l-3 3" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                        Stuur resetlink
                    </button>

                    <a className="btn btn--ghost" href="/login">Terug naar inloggen</a>
                </form>

                <footer className="card__footer">
                    <p className="muted">Geen mail ontvangen? Controleer spam of <a className="link" href="#">contacteer support</a>.</p>
                </footer>
            </main>
        </div>
    )
}
