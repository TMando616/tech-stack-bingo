export interface User { id: number; name: string; email: string; }
export interface BingoBoard { id: number; title: string; }
export interface BingoItem { id: number; label: string; is_achieved: boolean; achieved_at: string | null; position: number; }
