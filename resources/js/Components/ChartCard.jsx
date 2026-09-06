import { useEffect, useRef } from 'react';
import Chart from 'chart.js/auto';

const palette = ['#e97817', '#3678b8', '#2e9b73', '#8b5cf6', '#d64b4b'];

const legendColor = (dataset, type, index) => {
    const backgroundColor = Array.isArray(dataset.backgroundColor)
        ? dataset.backgroundColor[index]
        : dataset.backgroundColor;
    const borderColor = Array.isArray(dataset.borderColor)
        ? dataset.borderColor[index]
        : dataset.borderColor;

    return type === 'line' ? (borderColor ?? backgroundColor) : (backgroundColor ?? borderColor);
};

export default function ChartCard({
    id,
    type,
    labels = [],
    datasets = [],
    values,
    label,
    options = {},
    height = '280px',
    emptyMessage = 'No data available',
    title,
    description,
    footer,
}) {
    const canvas = useRef(null);
    const defaultDataset = values ? [{ label, data: values }] : [];
    const chartDatasets = (datasets.length > 0 ? datasets : defaultDataset).map((dataset, index) => ({
        borderWidth: type === 'doughnut' ? 0 : 2,
        borderRadius: type === 'bar' ? 6 : 0,
        tension: 0.35,
        pointRadius: type === 'line' ? 3 : 0,
        pointBorderWidth: type === 'line' ? 2 : 0,
        borderColor: palette[index % palette.length],
        backgroundColor: type === 'line' ? `${palette[index % palette.length]}24` : palette[index % palette.length],
        ...dataset,
    }));
    const hasData = chartDatasets.some((dataset) => dataset.data?.some((value) => value !== null && value !== undefined));
    const isCircular = type === 'doughnut' || type === 'pie';
    const legendItems = isCircular && chartDatasets.length === 1
        ? labels.map((legendLabel, index) => ({ label: legendLabel, color: legendColor(chartDatasets[0], type, index) }))
        : chartDatasets
            .map((dataset, index) => ({ label: dataset.label, color: legendColor(dataset, type, index) }))
            .filter((item) => item.label);

    useEffect(() => {
        if (!canvas.current || !hasData) return undefined;

        const defaultScales = isCircular ? {} : {
            x: { grid: { color: 'rgba(220, 229, 239, 0.6)' }, ticks: { font: { family: 'Poppins', size: 10 } } },
            y: { beginAtZero: true, grid: { color: 'rgba(220, 229, 239, 0.6)' }, ticks: { font: { family: 'Poppins', size: 10 } } },
        };
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            ...options,
            plugins: {
                legend: {
                    display: false,
                    ...options.plugins?.legend,
                },
                tooltip: {
                    ...options.plugins?.tooltip,
                    callbacks: {
                        label: (context) => {
                            const numericValue = Number(context.raw);
                            const value = Number.isInteger(numericValue) ? String(numericValue) : numericValue.toFixed(1);
                            const count = context.dataset.valueCounts?.[context.dataIndex];
                            const suffix = count === undefined ? '' : ` (${count} ratings)`;
                            return `${context.dataset.label ? `${context.dataset.label}: ` : ''}${value}${suffix}`;
                        },
                        ...options.plugins?.tooltip?.callbacks,
                    },
                },
            },
            scales: { ...defaultScales, ...options.scales },
        };

        const chart = new Chart(canvas.current, {
            type,
            data: { labels, datasets: chartDatasets },
            options: chartOptions,
        });

        return () => chart.destroy();
    }, [chartDatasets, hasData, labels, options, type]);

    return (
        <div className="card mb-4 dashboard-chart-card">
            {(title || description) && (
                <div className="card-header dashboard-chart-card__header">
                    {title && <h5 className="mb-0">{title}</h5>}
                    {description && <p className="mb-0 text-muted small">{description}</p>}
                </div>
            )}
            <div className="card-body chart-container" style={{ height }}>
                {hasData ? (
                    <>
                        {legendItems.length > 0 && (
                            <div className="chart-legend" role="list">
                                {legendItems.map((item, index) => (
                                    <span className="chart-legend__item" role="listitem" key={`${item.label}-${index}`}>
                                        <span className="chart-legend__swatch" style={{ backgroundColor: item.color }} aria-hidden="true" />
                                        <span>{item.label}</span>
                                    </span>
                                ))}
                            </div>
                        )}
                        <div className="chart-canvas-container"><canvas id={id} ref={canvas} /></div>
                    </>
                ) : <div className="chart-empty-state">{emptyMessage}</div>}
            </div>
            {footer && <div className="card-footer dashboard-chart-card__footer">{footer}</div>}
        </div>
    );
}
