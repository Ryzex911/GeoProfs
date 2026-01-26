import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ balance, lopendeAanvragen }) {
    // Simple fallback values
    const remainingDays = balance?.remaining_days ?? 25;
    const usedDays = balance?.used_days ?? 0;
    const startDays = balance?.start_days ?? 25;
    const year = balance?.year ?? 2026;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Saldo Card */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Verlof saldo {year}</h3>

                                {/* Saldo KPI (groot getal) */}
                                <div className="text-center mb-6">
                                    <div className="text-sm text-gray-600 mb-2">Resterend</div>
                                    <div className="text-4xl font-bold text-blue-600">
                                        {remainingDays} <span className="text-lg">dagen</span>
                                    </div>
                                </div>

                                {/* Details */}
                                <div className="bg-gray-50 p-3 rounded-lg mb-4 text-sm">
                                    <div className="flex justify-between mb-1">
                                        <span>Startsaldo:</span> <span>{startDays} dagen</span>
                                    </div>
                                    <div className="flex justify-between mb-1">
                                        <span>Gebruikt:</span> <span>{usedDays} dagen</span>
                                    </div>
                                    <div className="flex justify-between font-semibold">
                                        <span>Resterend:</span> <span>{remainingDays} dagen</span>
                                    </div>
                                </div>

                                {/* Waarschuwing */}
                                {remainingDays < 3 && (
                                    <div className="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4 text-sm text-yellow-800">
                                        ⚠️ Je saldo is laag. Plan je verlof voorzichtig!
                                    </div>
                                )}

                                <p className="text-xs text-gray-500">
                                    Gegevens worden automatisch bijgewerkt.
                                </p>
                            </div>
                        </div>

                        {/* Other Dashboard Content */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Overzicht</h3>
                                <p className="text-gray-600">Lopende aanvragen: {lopendeAanvragen || 0}</p>
                                <p className="text-gray-600 mt-2">Welkom op je dashboard!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
