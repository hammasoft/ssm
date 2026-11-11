import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { Pagination, Stars, UserChip } from './ui';

describe('composants UI', () => {
  it('affiche la note sous forme d\'étoiles accessibles', () => {
    render(<Stars value={4.4} count={12} />);
    expect(screen.getByLabelText('Note 4.4 sur 5')).toHaveTextContent('★★★★');
    expect(screen.getByText('(12)')).toBeInTheDocument();
  });

  it('lie le chip utilisateur vers son profil', () => {
    render(
      <MemoryRouter>
        <UserChip user={{ id: 7, username: 'alice', ratingAvg: 5, ratingCount: 1 }} />
      </MemoryRouter>,
    );
    expect(screen.getByRole('link')).toHaveAttribute('href', '/members/7');
  });

  it('masque la pagination quand il n\'y a qu\'une page', () => {
    const { container } = render(<Pagination page={0} totalPages={1} onChange={() => undefined} />);
    expect(container).toBeEmptyDOMElement();
  });
});
