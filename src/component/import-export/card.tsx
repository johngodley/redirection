import type { ReactNode } from 'react';
import clsx from 'clsx';

interface CardMetaItem {
	label: string;
	value: ReactNode;
	description?: ReactNode;
}

interface CardStatItem {
	label: string;
	value: string | number;
}

interface IoCardProps {
	title: ReactNode;
	badge?: ReactNode;
	meta?: CardMetaItem[];
	stats?: CardStatItem[];
	className?: string;
	actions?: ReactNode;
	children?: ReactNode;
	variant?: 'default' | 'success';
	wrapped?: boolean;
}

function IoCard( {
	title,
	badge,
	meta = [],
	stats = [],
	className = '',
	actions,
	children,
	variant = 'default',
	wrapped = true,
}: IoCardProps ) {
	const content = (
		<>
			<div className="file-sniff__header">
				<div className="file-sniff__name">{ title }</div>
				{ badge && <div className="file-sniff__badge">{ badge }</div> }
			</div>
			{ meta.length > 0 && (
				<div className="file-sniff__meta">
					{ meta.map( ( detail ) => (
						<div className="file-sniff__meta-row" key={ detail.label }>
							<div className="file-sniff__meta-label">{ detail.label }</div>
							<div className="file-sniff__meta-value">{ detail.value }</div>
							{ detail.description && (
								<div className="file-sniff__meta-description">{ detail.description }</div>
							) }
						</div>
					) ) }
				</div>
			) }
			{ stats.length > 0 && (
				<div className="file-sniff__stats">
					{ stats.map( ( stat ) => (
						<div className="file-sniff__stat" key={ stat.label }>
							<div className="file-sniff__stat-value">{ stat.value }</div>
							<div className="file-sniff__stat-label">{ stat.label }</div>
						</div>
					) ) }
				</div>
			) }
			{ children }
			{ actions && <div className="import-source-card__actions">{ actions }</div> }
		</>
	);

	if ( ! wrapped ) {
		return content;
	}

	return (
		<div
			className={ clsx( 'file-sniff__card', className, {
				'file-sniff__card--success': variant === 'success',
			} ) }
		>
			{ content }
		</div>
	);
}

export type { CardMetaItem, CardStatItem };
export default IoCard;
