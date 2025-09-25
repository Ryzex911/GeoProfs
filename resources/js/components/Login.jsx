import React from 'react'
import '../../css/login.css'

export default function Login() {
    return (
        <div className="page page--auth">
            <aside className="visual">
                <div className="visual__overlay" />
                <div className="visual__brand">
                    {/* vervang door je eigen logo */}
                    <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true" className="logo">
                        <path d="M3 12l6-6 6 6-6 6-6-6z" fill="none" stroke="currentColor" strokeWidth="1.5"/>
                        <path d="M9 6l6 6" stroke="currentColor" strokeWidth="1.5" fill="none" />
                    </svg>
                    <span>GeoProfs</span>
                </div>
            </aside>

            <main className="card">
                <header className="card__header">
                    <h1>Inloggen</h1>
                    <p className="muted">Welkom terug. Log in om verder te gaan.</p>
                </header>

                {/* puur design; geen submit */}
                <form className="form" onSubmit={(e)=>e.preventDefault()}>
                    <div className="field">
                        <label htmlFor="email">E-mailadres</label>
                        <input id="email" name="email" type="email" placeholder="naam@bedrijf.nl" />
                        <small className="hint">Gebruik je zakelijke e-mail.</small>
                    </div>

                    <div className="field">
                        <label htmlFor="password">Wachtwoord</label>
                        <input id="password" name="password" type="password" placeholder="••••••••" />
                        <div className="field__row">
                            <label className="checkbox">
                                <input type="checkbox" />
                                <span>Onthoud mij</span>
                            </label>
                            <a className="link" href="/reset-password">Wachtwoord vergeten?</a>
                        </div>
                    </div>

                    <button className="btn btn--primary" type="button">
                        <svg width="18" height="18" viewBox="0 0 24 24" className="btn__icon" aria-hidden="true">
                            <path d="M3 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                        Inloggen

                        {/*//hier moet nog de redirectie naar de check functie als de gegevens kloppen en
                        // waar naar toe wordt verwezen */}
                    </button>
                </form>

                <footer className="card__footer">
                    <p className="muted">Problemen met inloggen? <a className="link" href="#">Neem contact op</a>.</p>
                </footer>
            </main>
        </div>
    )
}
