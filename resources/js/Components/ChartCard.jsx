import { useEffect, useRef } from 'react';
import Chart from 'chart.js/auto';

export default function ChartCard({ id, type, labels, values, label }) {
    const canvas = useRef(null);

    useEffect(() => {
        if (!canvas.current) return undefined;
        const chart = new Chart(canvas.current, {
            type,
            data: {
                labels,
                datasets: [{
                    label,
                    data: values,
                    borderColor: '#e97817',
                    backgroundColor: type === 'line' ? 'rgba(233, 120, 23, 0.14)' : 'rgba(54, 120, 184, 0.72)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#e97817',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    borderWidth: 2,
                    borderRadius: type === 'bar' ? 6 : 0,
                    tension: 0.35,
                    fill: type === 'line',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { font: { family: 'Poppins', size: 11 } },
                    },
                },
                scales: {
                    x: { grid: { color: 'rgba(220, 229, 239, 0.6)' }, ticks: { font: { family: 'Poppins', size: 10 } } },
                    y: { grid: { color: 'rgba(220, 229, 239, 0.6)' }, ticks: { font: { family: 'Poppins', size: 10 } } },
                },
            },
        });
        return () => chart.destroy();
    }, [type, labels, values, label]);

    return <div className="card mb-4"><div className="card-body chart-container"><canvas id={id} ref={canvas} /></div></div>;
}
